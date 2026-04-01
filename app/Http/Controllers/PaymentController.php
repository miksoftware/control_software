<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Client;
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
        $query = Payment::with('client');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('category')) {
            $query->forCategory($request->input('category'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

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
        $client = Client::findOrFail($request->validated('client_id'));

        try {
            $payment = $this->paymentService->registerPayment($client, $request->validated());
        } catch (\InvalidArgumentException|\LogicException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payment->load('client');

        return response()->json([
            'success'           => true,
            'message'           => 'Pago registrado exitosamente.',
            'data'              => $payment,
            'financial_summary' => $this->paymentService->getFinancialSummary($client),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un pago específico.
     */
    public function show(Payment $payment): JsonResponse
    {
        $payment->load('client');

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
     * Obtener el resumen financiero de un cliente.
     */
    public function financialSummary(Request $request): JsonResponse
    {
        $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
        ]);

        $client = Client::findOrFail($request->integer('client_id'));

        return response()->json([
            'success'           => true,
            'financial_summary' => $this->paymentService->getFinancialSummary($client),
            'payment_history'   => $this->paymentService->getPaymentHistory($client),
        ]);
    }
}
