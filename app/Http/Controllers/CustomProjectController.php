<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Requests\StoreCustomProjectRequest;
use App\Models\CustomProject;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomProjectController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * Listar proyectos a la medida con filtros opcionales.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CustomProject::with('client');

        // Filtro por cliente
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->withStatus(ProjectStatus::from($request->input('status')));
        }

        // Solo con saldo pendiente
        if ($request->boolean('with_balance')) {
            $query->withOutstandingBalance();
        }

        // Solo retrasados
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
     * Mostrar un proyecto específico con pagos e información financiera.
     */
    public function show(CustomProject $project): JsonResponse
    {
        $project->load(['client', 'payments']);

        // Agregar atributos calculados
        $project->append([
            'total_paid',
            'outstanding_balance',
            'payment_progress',
            'is_fully_paid',
            'is_overdue',
        ]);

        return response()->json([
            'success'           => true,
            'data'              => $project,
            'financial_summary' => $this->paymentService->getFinancialSummary($project),
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

        // Si se marca como completado, registrar fecha de fin real
        if (
            isset($validated['status']) &&
            $validated['status'] === ProjectStatus::Completed->value &&
            empty($validated['actual_end_date'])
        ) {
            $validated['actual_end_date'] = now()->toDateString();
        }

        // Validar que el nuevo contract_value no sea menor a lo ya pagado
        if (isset($validated['contract_value'])) {
            $totalPaid = $project->total_paid;
            if ((float) $validated['contract_value'] < $totalPaid) {
                return response()->json([
                    'success' => false,
                    'message' => sprintf(
                        'El valor del contrato no puede ser menor a lo ya pagado ($%s).',
                        number_format($totalPaid, 2)
                    ),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
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
        // Verificar que no tenga pagos registrados
        if ($project->payments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un proyecto que tiene pagos registrados. Considere cancelarlo.',
            ], Response::HTTP_CONFLICT);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proyecto eliminado exitosamente.',
        ]);
    }
}
