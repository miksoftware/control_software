<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\ClientType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Client::query();

        if ($request->filled('client_type')) {
            $query->where('client_type', $request->input('client_type'));
        }

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        $clients = $query->withCount(['mikposLicenses', 'mikposFeatures', 'customProjects'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        $clientTypes = ClientType::cases();
        return view('clients.create', compact('clientTypes'));
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        Client::create($request->validated());

        return redirect()->route('clients.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Client $client): View
    {
        $client->load([
            'mikposLicenses' => fn ($q) => $q->orderByDesc('created_at'),
            'mikposFeatures' => fn ($q) => $q->orderByDesc('created_at'),
            'customProjects' => fn ($q) => $q->orderByDesc('created_at'),
            'payments'       => fn ($q) => $q->orderByDesc('paid_at'),
        ]);

        return view('clients.show', compact('client'));
    }

    public function statement(Client $client): View
    {
        $client->load([
            'mikposFeatures' => fn ($q) => $q->orderByDesc('created_at'),
            'customProjects' => fn ($q) => $q->orderByDesc('created_at'),
            'payments'       => fn ($q) => $q->orderByDesc('paid_at'),
        ]);

        return view('clients.statement', compact('client'));
    }

    public function edit(Client $client): View
    {
        $clientTypes = ClientType::cases();
        return view('clients.edit', compact('client', 'clientTypes'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()->route('clients.show', $client)
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($client->mikposLicenses()->where('status', 'active')->exists()) {
            return back()->with('error', 'No se puede eliminar un cliente con licencias activas.');
        }

        if ($client->customProjects()->active()->exists()) {
            return back()->with('error', 'No se puede eliminar un cliente con proyectos activos.');
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }
}
