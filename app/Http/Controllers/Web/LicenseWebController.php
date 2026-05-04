<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\BillingCycle;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMikposLicenseRequest;
use App\Models\Client;
use App\Models\MikposLicense;
use App\Services\ResellerLicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class LicenseWebController extends Controller
{
    public function __construct(
        private readonly ResellerLicenseService $resellerService,
    ) {}

    public function index(Request $request): View
    {
        $query = MikposLicense::with('client')->withCount('features');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('is_free_promotion') && $request->input('is_free_promotion') !== '') {
            $query->where('is_free_promotion', $request->boolean('is_free_promotion'));
        }

        $licenses = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Pre-load reseller summaries for all reseller clients in current page
        $resellerSummaries = [];
        $resellerClients = $licenses->getCollection()
            ->pluck('client')
            ->unique('id')
            ->filter(fn (Client $c) => $c->is_reseller);

        foreach ($resellerClients as $client) {
            $resellerSummaries[$client->id] = $this->resellerService->getResellerSummary($client);
        }

        $clients       = Client::orderBy('name')->get();
        $billingCycles = BillingCycle::cases();

        return view('licenses.index', compact('licenses', 'clients', 'billingCycles', 'resellerSummaries'));
    }

    public function create(): View
    {
        $clients       = Client::orderBy('name')->get();
        $billingCycles = BillingCycle::cases();

        return view('licenses.create', compact('clients', 'billingCycles'));
    }

    public function store(StoreMikposLicenseRequest $request): RedirectResponse
    {
        $client = Client::findOrFail($request->validated('client_id'));
        $data   = $request->validated();
        unset($data['client_id']);

        try {
            $license = $this->resellerService->createLicense($client, $data);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = $license->is_free_promotion
            ? '🎉 ¡Licencia creada como GRATUITA por promoción de revendedor!'
            : 'Licencia creada exitosamente.';

        return redirect()->route('licenses.index')
            ->with('success', $message);
    }

    public function show(MikposLicense $license): RedirectResponse
    {
        return redirect()->route('licenses.index');
    }

    public function edit(MikposLicense $license): RedirectResponse
    {
        return redirect()->route('licenses.index');
    }

    public function update(Request $request, MikposLicense $license): RedirectResponse
    {
        $validated = $request->validate([
            'billing_cycle'   => ['sometimes', 'string'],
            'monthly_rate'    => ['sometimes', 'numeric', 'min:0'],
            'status'          => ['sometimes', 'string'],
            'site_url'        => ['nullable', 'url', 'max:500'],
            'system_token'    => ['nullable', 'string', 'max:255'],
            'next_billing_at' => ['nullable', 'date'],
        ]);

        if ($license->is_free_promotion && isset($validated['monthly_rate']) && $validated['monthly_rate'] > 0) {
            return back()->with('error', 'No se puede asignar tarifa a una licencia gratuita por promoción.');
        }

        // Si el campo system_token viene vacío, no se toca el token existente
        if (array_key_exists('system_token', $validated) && ($validated['system_token'] === null || $validated['system_token'] === '')) {
            unset($validated['system_token']);
        }

        $license->update($validated);

        return redirect()->route('licenses.index')
            ->with('success', 'Licencia actualizada exitosamente.');
    }

    public function toggleSystem(Request $request, MikposLicense $license): RedirectResponse
    {
        $request->validate([
            'action' => ['nullable', 'in:enable,disable'],
        ]);

        if (!$license->site_url) {
            return back()->with('error', 'Esta licencia no tiene URL de sitio configurada.');
        }

        if (!$license->system_token) {
            return back()->with('error', 'No hay token de sistema configurado para esta licencia. Configúralo en "Editar Licencia".');
        }

        $payload = ['token' => $license->system_token];
        if ($request->filled('action')) {
            $payload['action'] = $request->input('action');
        }

        try {
            $response = Http::timeout(10)
                ->asJson()
                ->post(rtrim($license->site_url, '/') . '/api/system/toggle', $payload);

            if ($response->status() === 401) {
                return back()->with('error', 'Token no autorizado. Asegúrate de que el token guardado aquí sea exactamente igual al valor de SYSTEM_ADMIN_TOKEN en el archivo .env del sitio remoto.');
            }

            if ($response->successful() && $response->json('success')) {
                $status = $response->json('status');
                $license->update(['system_enabled' => $status === 'enabled']);

                $message = $status === 'enabled'
                    ? 'Sistema habilitado correctamente.'
                    : 'Sistema deshabilitado correctamente.';

                return back()->with('success', $message);
            }

            return back()->with('error', 'Respuesta inesperada del sistema remoto.');
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return back()->with('error', 'No se pudo conectar al sitio remoto. Verifique que la URL sea correcta y que el servidor esté activo.');
        }
    }

    public function destroy(MikposLicense $license): RedirectResponse
    {
        $license->delete();

        return redirect()->route('licenses.index')
            ->with('success', 'Licencia eliminada exitosamente.');
    }
}
