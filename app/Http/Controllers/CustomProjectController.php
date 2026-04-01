<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Requests\StoreCustomProjectRequest;
use App\Models\CustomProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomProjectController extends Controller
{
    /**
     * Listar proyectos a la medida con filtros opcionales.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CustomProject::with('client');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('status')) {
            $query->withStatus(ProjectStatus::from($request->input('status')));
        }

        if ($request->boolean('with_balance')) {
            $query->withOutstandingBalance();
        }

        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        $projects = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $projects,
        ]);
    }

    /**
     * Crear un nuevo proyecto a la medida.
     */
    public function store(StoreCustomProjectRequest $request): JsonResponse
    {
        $project = CustomProject::create($request->validated());

        $project->load('client');

        return response()->json([
            'success' => true,
            'message' => 'Proyecto creado exitosamente.',
            'data'    => $project,
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un proyecto específico.
     */
    public function show(CustomProject $project): JsonResponse
    {
        $project->load('client');

        $project->append('is_overdue');

        return response()->json([
            'success' => true,
            'data'    => $project,
        ]);
    }

    /**
     * Actualizar un proyecto existente.
     */
    public function update(Request $request, CustomProject $project): JsonResponse
    {
        $validated = $request->validate([
            'name'               => ['sometimes', 'required', 'string', 'max:255'],
            'description'        => ['nullable', 'string', 'max:5000'],
            'contract_value'     => ['sometimes', 'numeric', 'min:0'],
            'status'             => ['sometimes', 'string'],
            'start_date'         => ['nullable', 'date'],
            'estimated_end_date' => ['nullable', 'date'],
            'actual_end_date'    => ['nullable', 'date'],
        ]);

        if (
            isset($validated['status']) &&
            $validated['status'] === ProjectStatus::Completed->value &&
            empty($validated['actual_end_date'])
        ) {
            $validated['actual_end_date'] = now()->toDateString();
        }

        $project->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Proyecto actualizado exitosamente.',
            'data'    => $project->fresh(['client']),
        ]);
    }

    /**
     * Eliminar un proyecto (soft delete).
     */
    public function destroy(CustomProject $project): JsonResponse
    {
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proyecto eliminado exitosamente.',
        ]);
    }
}
