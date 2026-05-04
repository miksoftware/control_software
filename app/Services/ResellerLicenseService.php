<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ClientType;
use App\Enums\LicenseStatus;
use App\Models\Client;
use App\Models\MikposLicense;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ResellerLicenseService
{
    /**
     * Cantidad de licencias pagadas necesarias antes de otorgar una gratuita.
     */
    private const int PAID_LICENSES_FOR_FREE = 4;

    /**
     * Crea una nueva licencia MikPoS para un cliente.
     *
     * Si el cliente es Revendedor, evalúa automáticamente si la nueva licencia
     * debe ser gratuita (cada 5ta licencia: la 5ta, la 10ma, etc.).
     *
     * @param  Client               $client  El cliente al que se le asignará la licencia.
     * @param  array<string, mixed> $data    Datos de la licencia (billing_cycle, monthly_rate, installation_fee, etc.).
     * @return MikposLicense                 La licencia creada.
     *
     * @throws InvalidArgumentException Si se intenta forzar is_free_promotion en un cliente final.
     */
    public function createLicense(Client $client, array $data): MikposLicense
    {
        // Validar que no se fuerce promoción en clientes finales
        if (
            isset($data['is_free_promotion']) &&
            $data['is_free_promotion'] === true &&
            $client->client_type !== ClientType::Reseller
        ) {
            throw new InvalidArgumentException(
                'La promoción de licencia gratuita solo aplica para clientes de tipo Revendedor.'
            );
        }

        return DB::transaction(function () use ($client, $data): MikposLicense {
            // Determinar si aplica promoción gratuita
            $isFreePromotion = $this->shouldBeFreeLicense($client);

            // Si es gratuita por promoción, forzar costos a 0
            if ($isFreePromotion) {
                $data['monthly_rate']      = 0;
                $data['installation_fee']  = 0;
                $data['is_free_promotion'] = true;
            } else {
                $data['is_free_promotion'] = false;
                // Asignar valores por defecto si no se proporcionan
                $data['monthly_rate']     = $data['monthly_rate'] ?? 50000;
                $data['installation_fee'] = $data['installation_fee'] ?? 0;
            }

            // Asignar estado activo y fecha de activación por defecto
            $data['status']       = $data['status'] ?? LicenseStatus::Active;
            $data['activated_at'] = $data['activated_at'] ?? now()->toDateString();

            return $client->mikposLicenses()->create($data);
        });
    }

    /**
     * Determina si la siguiente licencia del revendedor debe ser gratuita.
     *
     * Regla de negocio:
     * - Solo aplica a clientes Revendedores.
     * - Por cada 4 licencias PAGADAS, la siguiente (5ta) es gratuita.
     * - Patrón: Licencias 1-4 pagadas → 5ta gratis, 6-9 pagadas → 10ta gratis, etc.
     * - Utiliza lock pessimista para prevenir condiciones de carrera.
     *
     * @param  Client $client El cliente a evaluar.
     * @return bool           True si la siguiente licencia debe ser gratuita.
     */
    public function shouldBeFreeLicense(Client $client): bool
    {
        // Solo aplica a revendedores
        if ($client->client_type !== ClientType::Reseller) {
            return false;
        }

        // Contar licencias PAGADAS del revendedor (con lock para evitar race conditions)
        $paidCount = $client->mikposLicenses()
            ->lockForUpdate()
            ->where('is_free_promotion', false)
            ->count();

        // Contar licencias GRATUITAS ya otorgadas
        $freeCount = $client->mikposLicenses()
            ->lockForUpdate()
            ->where('is_free_promotion', true)
            ->count();

        // Por cada 4 pagadas se gana 1 gratuita
        // La 5ta, 10ma, 15va, etc. serán gratuitas
        $earnedFree = intdiv($paidCount, self::PAID_LICENSES_FOR_FREE);

        return $earnedFree > 0 && $freeCount < $earnedFree;
    }

    /**
     * Obtiene un resumen de las licencias del revendedor.
     *
     * @param  Client                $client El revendedor.
     * @return array<string, mixed>          Resumen con conteos y próxima licencia gratuita.
     */
    public function getResellerSummary(Client $client): array
    {
        if ($client->client_type !== ClientType::Reseller) {
            throw new InvalidArgumentException(
                'El resumen de revendedor solo está disponible para clientes de tipo Revendedor.'
            );
        }

        $paidCount = $client->mikposLicenses()
            ->where('is_free_promotion', false)
            ->count();

        $freeCount = $client->mikposLicenses()
            ->where('is_free_promotion', true)
            ->count();

        $totalCount = $paidCount + $freeCount;

        // Por cada 4 pagadas se gana 1 gratuita
        $earnedFree = intdiv($paidCount, self::PAID_LICENSES_FOR_FREE);

        // La siguiente es gratis si hay gratuitas ganadas pendientes de otorgar
        $nextIsFree = $earnedFree > 0 && $freeCount < $earnedFree;

        // Calcular cuántas licencias pagadas faltan para la próxima gratuita
        $remainingForFree = $nextIsFree
            ? 0
            : self::PAID_LICENSES_FOR_FREE - ($paidCount % self::PAID_LICENSES_FOR_FREE);

        return [
            'total_licenses'            => $totalCount,
            'paid_licenses'             => $paidCount,
            'free_licenses'             => $freeCount,
            'next_license_is_free'      => $nextIsFree,
            'remaining_for_next_free'   => $remainingForFree,
        ];
    }
}
