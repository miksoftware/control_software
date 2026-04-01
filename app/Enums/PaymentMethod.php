<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Transfer = 'transfer';
    case Card = 'card';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Efectivo',
            self::Transfer => 'Transferencia',
            self::Card => 'Tarjeta',
            self::Other => 'Otro',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cash => 'banknotes',
            self::Transfer => 'arrow-path',
            self::Card => 'credit-card',
            self::Other => 'ellipsis-horizontal',
        };
    }
}
