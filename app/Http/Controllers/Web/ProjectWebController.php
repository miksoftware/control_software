<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomProjectRequest;
use App\Models\Client;
use App\Models\CustomProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = CustomProject::with('client');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('status')) {
            $query->withStatus(ProjectStatus::from($request->input('status')));
        }

        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        $projects = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $clients  = Client::orderBy('name')->get();
        $statuses = ProjectStatus::cases();

        return view('projects.index', compact('projects', 'clients', 'statuses'));
    }

    public function create(): View
    {
        $clients  = Client::orderBy('name')->get();
        $statuses = ProjectStatus::cases();

        return view('projects.create', compact('clients', 'statuses'));
    }

    public function store(StoreCustomProjectRequest $request): RedirectResponse
    {
        $project = CustomProject::create($request->validated());

        return redirect()->route('projects.show', $project)
            ->with('success', 'Proyecto creado exitosamente.');
    }

    public function show(CustomProject $project): View
    {
        $project->load(['client', 'payments']);

        return view('projects.show', compact('project'));
    }

    public function edit(CustomProject $project): View
    {
        $clients  = Client::orderBy('name')->get();
        $statuses = ProjectStatus::cases();

        return view('projects.edit', compact('project', 'clients', 'statuses'));
    }

    public function update(Request $request, CustomProject $project): RedirectResponse
    {
        $validated = $request->validate([
            'name'               => ['sometimes', 'required', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'contract_value'     => ['sometimes', 'numeric', 'min:0'],
            'status'             => ['sometimes', 'string'],
            'start_date'         => ['nullable', 'date'],
            'estimated_end_date' => ['nullable', 'date'],
            'actual_end_date'    => ['nullable', 'date'],
        ]);

        if (isset($validated['status']) && $validated['status'] === ProjectStatus::Completed->value && empty($validated['actual_end_date'])) {
            $validated['actual_end_date'] = now()->toDateString();
        }

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Proyecto actualizado exitosamente.');
    }

    public function destroy(CustomProject $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Proyecto eliminado exitosamente.');
    }
}
