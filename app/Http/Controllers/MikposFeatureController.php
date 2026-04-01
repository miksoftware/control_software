<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Requests\StoreMikposFeatureRequest;
use App\Models\MikposFeature;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MikposFeatureController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * Listar mejoras/cambios con filtros opcionales.
     */
    public function index(Request $request): JsonResponse
    {
        $query = MikposFeature::with('client');

        // Filtro por cliente
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        // Filtro por licencia
        if ($request->filled('mikpos_license_id')) {
            $query->where('mikpos_license_id', $request->integer('mikpos_license_id'));
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->withStatus(ProjectStatus::from($request->input('status')));
        }

        // Solo con saldo pendiente
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
     * Mostrar una mejora específica con pagos e información financiera.
     */
    public function show(MikposFeature $feature): JsonResponse
    {
        $feature->load(['client', 'mikposLicense', 'payments']);

        // Agregar atributos calculados
        $feature->append([
            'total_paid',
            'outstanding_balance',
            'payment_progress',
            'is_fully_paid',
        ]);

        return response()->json([
            'success'           => true,
            'data'              => $feature,
            'financial_summary' => $this->paymentService->getFinancialSummary($feature),
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

        // Si se marca como completado, registrar fecha
        if (
            isset($validated['status']) &&
            $validated['status'] === ProjectStatus::Completed->value &&
            empty($validated['completed_at'])
        ) {
            $validated['completed_at'] = now()->toDateString();
        }

        // Validar que el nuevo total_cost no sea menor a lo ya pagado
        if (isset($validated['total_cost'])) {
            $totalPaid = $feature->total_paid;
            if ((float) $validated['total_cost'] < $totalPaid) {
                return response()->json([
                    'success' => false,
                    'message' => sprintf(
                        'El costo total no puede ser menor a lo ya pagado ($%s).',
                        number_format($totalPaid, 2)
                    ),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
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
        // Verificar que no tenga pagos registrados
        if ($feature->payments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una mejora que tiene pagos registrados. Considere cancelarla.',
            ], Response::HTTP_CONFLICT);
        }

        $feature->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mejora/cambio eliminado exitosamente.',
        ]);
    }
}
