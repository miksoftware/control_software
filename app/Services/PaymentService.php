<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

final class PaymentService
{
    /**
     * Categorías válidas de pago.
     *
     * @var list<string>
     */
    private const array VALID_CATEGORIES = ['global', 'projects', 'features'];

    /**
     * Registra un pago/abono contra la cuenta corriente de un cliente.
     *
     * @param  Client               $client  El cliente al que se le registra el pago.
     * @param  array<string, mixed> $data    Datos del pago (amount, category, payment_method, reference, notes, paid_at).
     * @return Payment                       El pago registrado.
     *
     * @throws InvalidArgumentException Si la categoría no es válida o el monto no es positivo.
     * @throws LogicException           Si el monto excede el saldo pendiente de la categoría.
     */
    public function registerPayment(Client $client, array $data): Payment
    {
        $category = $data['category'] ?? 'global';

        if (! in_array($category, self::VALID_CATEGORIES, true)) {
            throw new InvalidArgumentException(
                sprintf('La categoría "%s" no es válida. Use: %s', $category, implode(', ', self::VALID_CATEGORIES))
            );
        }

        return DB::transaction(function () use ($client, $data, $category): Payment {
            $amount = (float) $data['amount'];

            if ($amount <= 0) {
                throw new InvalidArgumentException('El monto del pago debe ser mayor a cero.');
            }

            // Validar saldo para categorías específicas (no global)
            if ($category !== 'global') {
                $pending = $this->getCategoryPendingBalance($client, $category);
                if ($amount > $pending) {
                    throw new LogicException(
                        sprintf(
                            'El monto del abono ($%s) excede el saldo pendiente de %s ($%s).',
                            number_format($amount, 2),
                            $category,
                            number_format($pending, 2)
                        )
                    );
                }
            }

            $data['paid_at']  = $data['paid_at'] ?? now()->toDateString();
            $data['category'] = $category;

            return $client->payments()->create($data);
        });
    }

    /**
     * Obtiene el saldo pendiente de una categoría específica para el cliente.
     */
    public function getCategoryPendingBalance(Client $client, string $category): float
    {
        $totalDebt = match ($category) {
            'projects' => (float) $client->customProjects()->sum('contract_value'),
            'features' => (float) $client->mikposFeatures()->sum('total_cost'),
            'global'   => (float) $client->customProjects()->sum('contract_value')
                        + (float) $client->mikposFeatures()->sum('total_cost'),
            default    => 0.0,
        };

        $totalPaid = match ($category) {
            'global' => (float) $client->payments()->sum('amount'),
            default  => (float) $client->payments()->where('category', $category)->sum('amount'),
        };

        return max(0, $totalDebt - $totalPaid);
    }

    /**
     * Obtiene el historial de pagos de un cliente.
     */
    public function getPaymentHistory(Client $client, ?string $category = null): Collection
    {
        $query = $client->payments();

        if ($category !== null) {
            $query->where('category', $category);
        }

        return $query->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Obtiene un resumen financiero del cliente.
     *
     * @return array<string, mixed>
     */
    public function getFinancialSummary(Client $client): array
    {
        $totalProjectsDebt  = (float) $client->customProjects()->sum('contract_value');
        $totalFeaturesDebt  = (float) $client->mikposFeatures()->sum('total_cost');
        $totalDebt          = $totalProjectsDebt + $totalFeaturesDebt;
        $totalPaid          = (float) $client->payments()->sum('amount');
        $balance            = max(0, $totalDebt - $totalPaid);
        $progress           = $totalDebt > 0 ? round(($totalPaid / $totalDebt) * 100, 2) : 100.0;

        return [
            'total_debt'             => $totalDebt,
            'total_projects_debt'    => $totalProjectsDebt,
            'total_features_debt'    => $totalFeaturesDebt,
            'total_paid'             => $totalPaid,
            'outstanding_balance'    => $balance,
            'payment_progress'       => min(100.0, $progress),
            'is_fully_paid'          => $balance <= 0,
            'payments_count'         => $client->payments()->count(),
        ];
    }
}
