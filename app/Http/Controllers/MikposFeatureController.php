<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Requests\StoreMikposFeatureRequest;
use App\Models\MikposFeature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MikposFeatureController extends Controller
{
    /**
     * Listar mejoras/cambios con filtros opcionales.
     */
    public function index(Request $request): JsonResponse
    {
        $query = MikposFeature::with('client');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('mikpos_license_id')) {
            $query->where('mikpos_license_id', $request->integer('mikpos_license_id'));
        }

        if ($request->filled('status')) {
            $query->withStatus(ProjectStatus::from($request->input('status')));
        }

        if ($request->boolean('with_balance')) {
            $query->withOutstandingBalance();
        }

        $features = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $features,
        ]);
    }

    /**
     * Crear una nueva mejora/cambio de MikPoS.
     */
    public function store(StoreMikposFeatureRequest $request): JsonResponse
    {
        $feature = MikposFeature::create($request->validated());

        $feature->load('client');

        return response()->json([
            'success' => true,
            'message' => 'Mejora/cambio registrado exitosamente.',
            'data'    => $feature,
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar una mejora específica.
     */
    public function show(MikposFeature $feature): JsonResponse
    {
        $feature->load(['client', 'mikposLicense']);

        return response()->json([
            'success' => true,
            'data'    => $feature,
        ]);
    }

    /**
     * Actualizar una mejora existente.
     */
    public function update(Request $request, MikposFeature $feature): JsonResponse
    {
        $validated = $request->validate([
            'title'                 => ['sometimes', 'required', 'string', 'max:255'],
            'description'           => ['nullable', 'string', 'max:5000'],
            'total_cost'            => ['sometimes', 'numeric', 'min:0'],
            'status'                => ['sometimes', 'string'],
            'estimated_delivery_at' => ['nullable', 'date'],
            'completed_at'          => ['nullable', 'date'],
        ]);

        if (
            isset($validated['status']) &&
            $validated['status'] === ProjectStatus::Completed->value &&
            empty($validated['completed_at'])
        ) {
            $validated['completed_at'] = now()->toDateString();
        }

        $feature->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Mejora/cambio actualizado exitosamente.',
            'data'    => $feature->fresh(['client']),
        ]);
    }

    /**
     * Eliminar una mejora (soft delete).
     */
    public function destroy(MikposFeature $feature): JsonResponse
    {
        $feature->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mejora/cambio eliminado exitosamente.',
        ]);
    }
}
