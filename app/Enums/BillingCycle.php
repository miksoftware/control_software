<?php

declare(strict_types=1);

namespace App\Enums;

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case SemiAnnual = 'semi_annual';
    case Annual = 'annual';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Mensual',
            self::Quarterly => 'Trimestral',
            self::SemiAnnual => 'Semestral',
            self::Annual => 'Anual',
        };
    }

    /**
     * Número de meses que comprende el ciclo de facturación.
     */
    public function months(): int
    {
        return match ($this) {
            self::Monthly => 1,
            self::Quarterly => 3,
            self::SemiAnnual => 6,
            self::Annual => 12,
        };
    }

    /**
     * Calcula el valor total del ciclo basándose en la tarifa mensual.
     */
    public function calculateCycleAmount(float $monthlyRate): float
    {
        return $monthlyRate * $this->months();
    }
}
