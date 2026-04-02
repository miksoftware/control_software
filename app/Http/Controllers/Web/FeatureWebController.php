<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMikposFeatureRequest;
use App\Models\Client;
use App\Models\MikposFeature;
use App\Models\MikposLicense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = MikposFeature::with('client');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('status')) {
            $query->withStatus(ProjectStatus::from($request->input('status')));
        }

        if ($request->boolean('with_balance')) {
            $query->withOutstandingBalance();
        }

        $features = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $statuses = ProjectStatus::cases();

        return view('features.index', compact('features', 'statuses'));
    }

    public function create(): View
    {
        $clients  = Client::orderBy('name')->get();
        $licenses = MikposLicense::with('client')->orderByDesc('created_at')->get();
        $statuses = ProjectStatus::cases();

        return view('features.create', compact('clients', 'licenses', 'statuses'));
    }

    public function store(StoreMikposFeatureRequest $request): RedirectResponse
    {
        $feature = MikposFeature::create($request->validated());

        return redirect()->route('features.show', $feature)
            ->with('success', 'Mejora/cambio registrado exitosamente.');
    }

    public function show(MikposFeature $feature): View
    {
        $feature->load(['client', 'mikposLicense']);
        $statuses = ProjectStatus::cases();

        return view('features.show', compact('feature', 'statuses'));
    }

    public function edit(MikposFeature $feature): View
    {
        $clients  = Client::orderBy('name')->get();
        $licenses = MikposLicense::with('client')->orderByDesc('created_at')->get();
        $statuses = ProjectStatus::cases();

        return view('features.edit', compact('feature', 'clients', 'licenses', 'statuses'));
    }

    public function update(Request $request, MikposFeature $feature): RedirectResponse
    {
        $validated = $request->validate([
            'title'                 => ['sometimes', 'required', 'string', 'max:255'],
            'description'           => ['nullable', 'string'],
            'total_cost'            => ['sometimes', 'numeric', 'min:0'],
            'status'                => ['sometimes', 'string'],
            'estimated_delivery_at' => ['nullable', 'date'],
            'completed_at'          => ['nullable', 'date'],
        ]);

        if (isset($validated['status']) && $validated['status'] === ProjectStatus::Completed->value && empty($validated['completed_at'])) {
            $validated['completed_at'] = now()->toDateString();
        }

        $feature->update($validated);

        return redirect()->route('features.show', $feature)
            ->with('success', 'Mejora/cambio actualizado exitosamente.');
    }

    public function destroy(MikposFeature $feature): RedirectResponse
    {
        $feature->delete();

        return redirect()->route('features.index')
            ->with('success', 'Mejora/cambio eliminado exitosamente.');
    }
}
