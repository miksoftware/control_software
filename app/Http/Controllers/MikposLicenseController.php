<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreMikposLicenseRequest;
use App\Models\Client;
use App\Models\MikposLicense;
use App\Services\ResellerLicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MikposLicenseController extends Controller
{
    public function __construct(
        private readonly ResellerLicenseService $resellerService,
    ) {}

    /**
     * Listar licencias con filtros opcionales.
     */
    public function index(Request $request): JsonResponse
    {
        $query = MikposLicense::with('client');

        // Filtro por cliente
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtro por tipo de promoción
        if ($request->has('is_free_promotion')) {
            $query->where('is_free_promotion', $request->boolean('is_free_promotion'));
        }

        // Filtro por ciclo de facturación
        if ($request->filled('billing_cycle')) {
            $query->where('billing_cycle', $request->input('billing_cycle'));
        }

        $licenses = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $licenses,
        ]);
    }

    /**
     * Crear una nueva licencia MikPoS.
     *
     * Utiliza el ResellerLicenseService para evaluar automáticamente
     * si aplica la promoción de 5ta licencia gratis para revendedores.
     */
    public function store(StoreMikposLicenseRequest $request): JsonResponse
    {
        $client = Client::findOrFail($request->validated('client_id'));
        $data   = $request->validated();

        // Eliminar client_id del array ya que se pasa por relación
        unset($data['client_id']);

        try {
            $license = $this->resellerService->createLicense($client, $data);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Cargar el cliente para la respuesta
        $license->load('client');

        // Preparar respuesta con info adicional para revendedores
        $responseData = [
            'success' => true,
            'message' => $license->is_free_promotion
                ? '¡Licencia creada como GRATUITA por promoción de revendedor!'
                : 'Licencia creada exitosamente.',
            'data'    => $license,
        ];

        // Si es revendedor, agregar resumen de promoción
        if ($client->is_reseller) {
            $responseData['reseller_summary'] = $this->resellerService->getResellerSummary($client);
        }

        return response()->json($responseData, Response::HTTP_CREATED);
    }

    /**
     * Mostrar una licencia específica con sus pagos.
     */
    public function show(MikposLicense $license): JsonResponse
    {
        $license->load(['client', 'features']);

        $license->append(['cycle_amount']);

        $responseData = [
            'success' => true,
            'data'    => $license,
        ];

        // Si es revendedor, agregar resumen
        if ($license->client->is_reseller) {
            $responseData['reseller_summary'] = $this->resellerService->getResellerSummary($license->client);
        }

        return response()->json($responseData);
    }

    /**
     * Actualizar una licencia existente.
     */
    public function update(Request $request, MikposLicense $license): JsonResponse
    {
        $validated = $request->validate([
            'billing_cycle'   => ['sometimes', 'string'],
            'monthly_rate'    => ['sometimes', 'numeric', 'min:0'],
            'status'          => ['sometimes', 'string'],
            'next_billing_at' => ['sometimes', 'nullable', 'date'],
        ]);

        // No permitir modificar is_free_promotion ni installation_fee en licencias gratuitas
        if ($license->is_free_promotion && isset($validated['monthly_rate']) && $validated['monthly_rate'] > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede asignar una tarifa a una licencia marcada como gratuita por promoción.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $license->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Licencia actualizada exitosamente.',
            'data'    => $license->fresh(['client']),
        ]);
    }

    /**
     * Eliminar una licencia (soft delete).
     */
    public function destroy(MikposLicense $license): JsonResponse
    {
        $license->delete();

        return response()->json([
            'success' => true,
            'message' => 'Licencia eliminada exitosamente.',
        ]);
    }
}
