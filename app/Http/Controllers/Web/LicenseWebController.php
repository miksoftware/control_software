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
use Illuminate\View\View;

class LicenseWebController extends Controller
{
    public function __construct(
        private readonly ResellerLicenseService $resellerService,
    ) {}

    public function index(Request $request): View
    {
        $query = MikposLicense::with('client');

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

        return view('licenses.index', compact('licenses'));
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

        return redirect()->route('licenses.show', $license)
            ->with('success', $message);
    }

    public function show(MikposLicense $license): View
    {
        $license->load(['client', 'features']);

        $resellerSummary = null;
        if ($license->client->is_reseller) {
            $resellerSummary = $this->resellerService->getResellerSummary($license->client);
        }

        return view('licenses.show', compact('license', 'resellerSummary'));
    }

    public function edit(MikposLicense $license): View
    {
        $clients       = Client::orderBy('name')->get();
        $billingCycles = BillingCycle::cases();

        return view('licenses.edit', compact('license', 'clients', 'billingCycles'));
    }

    public function update(Request $request, MikposLicense $license): RedirectResponse
    {
        $validated = $request->validate([
            'billing_cycle'   => ['sometimes', 'string'],
            'monthly_rate'    => ['sometimes', 'numeric', 'min:0'],
            'status'          => ['sometimes', 'string'],
            'next_billing_at' => ['nullable', 'date'],
        ]);

        if ($license->is_free_promotion && isset($validated['monthly_rate']) && $validated['monthly_rate'] > 0) {
            return back()->with('error', 'No se puede asignar tarifa a una licencia gratuita por promoción.');
        }

        $license->update($validated);

        return redirect()->route('licenses.show', $license)
            ->with('success', 'Licencia actualizada exitosamente.');
    }

    public function destroy(MikposLicense $license): RedirectResponse
    {
        $license->delete();

        return redirect()->route('licenses.index')
            ->with('success', 'Licencia eliminada exitosamente.');
    }
}
