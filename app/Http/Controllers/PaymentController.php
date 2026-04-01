<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\CustomProject;
use App\Models\MikposFeature;
use App\Models\MikposLicense;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * Listar pagos con filtros opcionales.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with('payable');

        // Filtro por tipo de entidad
        if ($request->filled('payable_type')) {
            $type = $this->resolvePayableType($request->input('payable_type'));
            $query->forType($type);
        }

        // Filtro por entidad específica
        if ($request->filled('payable_id')) {
            $query->where('payable_id', $request->integer('payable_id'));
        }

        // Filtro por método de pago
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        // Filtro por rango de fechas
        if ($request->filled('from') && $request->filled('to')) {
            $query->betweenDates($request->input('from'), $request->input('to'));
        }

        $payments = $query->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $payments,
        ]);
    }

    /**
     * Registrar un nuevo pago/abono.
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $payableType = $request->resolvedPayableType();
        $payableId   = $request->validated('payable_id');

        /** @var \Illuminate\Database\Eloquent\Model $payable */
        $payable = $payableType::findOrFail($payableId);

        try {
            $payment = $this->paymentService->registerPayment($payable, $request->validated());
        } catch (\InvalidArgumentException|\LogicException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payment->load('payable');

        // Obtener resumen financiero actualizado
        $financialSummary = $this->paymentService->getFinancialSummary($payable);

        return response()->json([
            'success'           => true,
            'message'           => 'Pago registrado exitosamente.',
            'data'              => $payment,
            'financial_summary' => $financialSummary,
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un pago específico.
     */
    public function show(Payment $payment): JsonResponse
    {
        $payment->load('payable');

        return response()->json([
            'success' => true,
            'data'    => $payment,
        ]);
    }

    /**
     * Eliminar un pago (soft delete).
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pago eliminado exitosamente.',
        ]);
    }

    /**
     * Obtener el resumen financiero de una entidad pagable.
     */
    public function financialSummary(Request $request): JsonResponse
    {
        $request->validate([
            'payable_type' => ['required', 'string'],
            'payable_id'   => ['required', 'integer', 'min:1'],
        ]);

        $payableType = $this->resolvePayableType($request->input('payable_type'));

        /** @var \Illuminate\Database\Eloquent\Model $payable */
        $payable = $payableType::findOrFail($request->integer('payable_id'));

        return response()->json([
            'success'           => true,
            'financial_summary' => $this->paymentService->getFinancialSummary($payable),
            'payment_history'   => $this->paymentService->getPaymentHistory($payable),
        ]);
    }

    /**
     * Resuelve el tipo de modelo a partir de un alias o FQCN.
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    private function resolvePayableType(string $type): string
    {
        return match ($type) {
            'license', MikposLicense::class  => MikposLicense::class,
            'feature', MikposFeature::class  => MikposFeature::class,
            'project', CustomProject::class  => CustomProject::class,
            default => $type,
        };
    }
}
