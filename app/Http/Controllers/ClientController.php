<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientReportService $clientReportService,
    ) {}
    /**
     * Listar todos los clientes con filtros opcionales.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Client::query();

        // Filtro por tipo de cliente
        if ($request->filled('client_type')) {
            $query->where('client_type', $request->input('client_type'));
        }

        // Búsqueda por nombre, email o empresa
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // Incluir relaciones si se solicita
        if ($request->boolean('with_licenses')) {
            $query->with('mikposLicenses');
        }

        if ($request->boolean('with_projects')) {
            $query->with('customProjects');
        }

        $clients = $query->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $clients,
        ]);
    }

    /**
     * Crear un nuevo cliente.
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cliente creado exitosamente.',
            'data'    => $client,
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un cliente específico con sus relaciones.
     */
    public function show(Client $client): JsonResponse
    {
        $client->load([
            'mikposLicenses' => fn ($q) => $q->orderByDesc('created_at'),
            'mikposFeatures' => fn ($q) => $q->orderByDesc('created_at'),
            'customProjects' => fn ($q) => $q->orderByDesc('created_at'),
        ]);

        // Agregar conteos calculados
        $client->append(['paid_licenses_count', 'total_licenses_count', 'is_reseller']);

        return response()->json([
            'success' => true,
            'data'    => $client,
        ]);
    }

    /**
     * Actualizar un cliente existente.
     */
    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado exitosamente.',
            'data'    => $client->fresh(),
        ]);
    }

    /**
     * Generar reporte detallado de un cliente.
     */
    public function report(Client $client): JsonResponse
    {
        $reportData = $this->clientReportService->generateReport($client);

        return response()->json([
            'success' => true,
            'data'    => $reportData,
            'message' => 'Reporte generado exitosamente.',
        ]);
    }

    /**
     * Eliminar un cliente (soft delete).
     */
    public function destroy(Client $client): JsonResponse
    {
        // Verificar que no tenga licencias activas
        if ($client->mikposLicenses()->where('status', 'active')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un cliente con licencias activas.',
            ], Response::HTTP_CONFLICT);
        }

        // Verificar que no tenga proyectos activos
        if ($client->customProjects()->active()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un cliente con proyectos activos.',
            ], Response::HTTP_CONFLICT);
        }

        $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado exitosamente.',
        ]);
    }
}
