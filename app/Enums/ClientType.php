<?php

declare(strict_types=1);

namespace App\Enums;

enum ClientType: string
{
    case Final = 'final';
    case Reseller = 'reseller';

    public function label(): string
    {
        return match ($this) {
            self::Final => 'Cliente Final',
            self::Reseller => 'Revendedor',
        };
    }

    public function isReseller(): bool
    {
        return $this === self::Reseller;
    }
}
