<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ClientType;
use App\Enums\LicenseStatus;
use App\Models\Client;
use Carbon\Carbon;

final class ClientReportService
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly ResellerLicenseService $resellerLicenseService,
    ) {}

    /**
     * Genera todos los datos del reporte para un cliente.
     *
     * @param  Client $client El cliente para el cual generar el reporte.
     * @return array<string, mixed> Datos completos del reporte.
     */
    public function generateReport(Client $client): array
    {
        // Eager loading de todas las relaciones necesarias
        $client->load([
            'mikposLicenses.features',
            'customProjects.payments',
            'payments.customProject',
            'mikposFeatures',
        ]);

        // Resumen financiero delegado a PaymentService
        $financialSummary = $this->paymentService->getFinancialSummary($client);

        // Subtotal mensual de licencias activas no gratuitas
        $activeMonthlyTotal = (float) $client->mikposLicenses
            ->filter(fn ($license) => $license->status === LicenseStatus::Active && !$license->is_free_promotion)
            ->sum('monthly_rate');

        // Mejoras huérfanas (sin licencia asociada)
        $orphanFeatures = $client->mikposFeatures
            ->filter(fn ($feature) => $feature->mikpos_license_id === null)
            ->values();

        // Pagos agrupados por categoría con subtotales
        $paymentsByCategory = $this->groupPaymentsByCategory($client);

        // Resumen de revendedor (solo si aplica)
        $resellerSummary = $client->client_type === ClientType::Reseller
            ? $this->resellerLicenseService->getResellerSummary($client)
            : null;

        return [
            'client'              => $client,
            'financial_summary'   => $financialSummary,
            'licenses'            => $client->mikposLicenses,
            'active_monthly_total'=> $activeMonthlyTotal,
            'projects'            => $client->customProjects,
            'orphan_features'     => $orphanFeatures,
            'payments_by_category'=> $paymentsByCategory,
            'reseller_summary'    => $resellerSummary,
            'generated_at'        => Carbon::now()->format('d/m/Y H:i'),
        ];
    }

    /**
     * Agrupa los pagos del cliente por categoría con subtotales.
     *
     * @param  Client $client
     * @return array<string, mixed>
     */
    private function groupPaymentsByCategory(Client $client): array
    {
        $grouped = $client->payments
            ->sortByDesc('paid_at')
            ->groupBy('category');

        $result = [];
        foreach (['global', 'projects', 'features'] as $category) {
            $payments = $grouped->get($category, collect());
            $result[$category] = [
                'payments' => $payments->values(),
                'subtotal' => (float) $payments->sum('amount'),
            ];
        }

        $result['grand_total'] = (float) $client->payments->sum('amount');

        return $result;
    }
}
