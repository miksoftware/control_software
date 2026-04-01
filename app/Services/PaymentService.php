<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

final class PaymentService
{
    /**
     * Modelos que soportan pagos polimórficos.
     *
     * @var list<class-string<Model>>
     */
    private const array PAYABLE_MODELS = [
        \App\Models\MikposLicense::class,
        \App\Models\MikposFeature::class,
        \App\Models\CustomProject::class,
    ];

    /**
     * Registra un pago/abono contra una entidad pagable.
     *
     * Valida que:
     * - El modelo sea de un tipo soportado.
     * - El monto no exceda el saldo pendiente.
     * - El monto sea positivo.
     *
     * @param  Model                 $payable La entidad a la que se le registra el pago.
     * @param  array<string, mixed>  $data    Datos del pago (amount, payment_method, reference, notes, paid_at).
     * @return Payment                        El pago registrado.
     *
     * @throws InvalidArgumentException Si el modelo no es de un tipo soportado.
     * @throws LogicException           Si el monto excede el saldo pendiente.
     */
    public function registerPayment(Model $payable, array $data): Payment
    {
        // Validar que el modelo soporte pagos
        $this->validatePayableModel($payable);

        return DB::transaction(function () use ($payable, $data): Payment {
            // Obtener saldo pendiente (con lock para concurrencia)
            $outstandingBalance = $this->getOutstandingBalance($payable);
            $amount = (float) $data['amount'];

            // Validar que el monto sea positivo
            if ($amount <= 0) {
                throw new InvalidArgumentException(
                    'El monto del pago debe ser mayor a cero.'
                );
            }

            // Validar que no exceda el saldo pendiente
            if ($amount > $outstandingBalance) {
                throw new LogicException(
                    sprintf(
                        'El monto del abono ($%s) excede el saldo pendiente ($%s).',
                        number_format($amount, 2),
                        number_format($outstandingBalance, 2)
                    )
                );
            }

            // Asignar fecha de pago por defecto (hoy)
            $data['paid_at'] = $data['paid_at'] ?? now()->toDateString();

            /** @var Payment $payment */
            $payment = $payable->payments()->create($data);

            return $payment;
        });
    }

    /**
     * Obtiene el saldo pendiente de una entidad pagable con lock pessimista.
     *
     * @param  Model $payable La entidad pagable.
     * @return float          El saldo pendiente.
     */
    public function getOutstandingBalance(Model $payable): float
    {
        $totalValue = $this->getTotalValue($payable);
        $totalPaid  = $this->getTotalPaid($payable);

        return max(0, $totalValue - $totalPaid);
    }

    /**
     * Obtiene el historial de pagos de una entidad pagable.
     *
     * @param  Model                                              $payable La entidad pagable.
     * @return \Illuminate\Database\Eloquent\Collection<Payment>           Los pagos asociados.
     */
    public function getPaymentHistory(Model $payable): \Illuminate\Database\Eloquent\Collection
    {
        $this->validatePayableModel($payable);

        return $payable->payments()
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Obtiene un resumen financiero de la entidad pagable.
     *
     * @param  Model                $payable La entidad pagable.
     * @return array<string, mixed>          Resumen con totales, pagos y saldo.
     */
    public function getFinancialSummary(Model $payable): array
    {
        $this->validatePayableModel($payable);

        $totalValue = $this->getTotalValue($payable);
        $totalPaid  = $this->getTotalPaid($payable);
        $balance    = max(0, $totalValue - $totalPaid);
        $progress   = $totalValue > 0 ? round(($totalPaid / $totalValue) * 100, 2) : 100.0;

        return [
            'total_value'         => $totalValue,
            'total_paid'          => $totalPaid,
            'outstanding_balance' => $balance,
            'payment_progress'    => min(100.0, $progress),
            'is_fully_paid'       => $balance <= 0,
            'payments_count'      => $payable->payments()->count(),
        ];
    }

    /**
     * Obtiene el valor total de la entidad (contrato/costo/ciclo).
     */
    private function getTotalValue(Model $payable): float
    {
        return match (true) {
            $payable instanceof \App\Models\MikposLicense => (float) $payable->installation_fee + $payable->cycle_amount,
            $payable instanceof \App\Models\MikposFeature => (float) $payable->total_cost,
            $payable instanceof \App\Models\CustomProject => (float) $payable->contract_value,
            default => throw new InvalidArgumentException('Modelo no soportado para cálculo de valor total.'),
        };
    }

    /**
     * Obtiene el total de pagos registrados contra la entidad.
     */
    private function getTotalPaid(Model $payable): float
    {
        return (float) $payable->payments()->lockForUpdate()->sum('amount');
    }

    /**
     * Valida que el modelo sea de un tipo soportado para pagos.
     *
     * @throws InvalidArgumentException Si no es un modelo payable válido.
     */
    private function validatePayableModel(Model $payable): void
    {
        $isValid = false;

        foreach (self::PAYABLE_MODELS as $modelClass) {
            if ($payable instanceof $modelClass) {
                $isValid = true;
                break;
            }
        }

        if (! $isValid) {
            throw new InvalidArgumentException(
                sprintf(
                    'El modelo "%s" no soporta pagos. Modelos válidos: %s',
                    get_class($payable),
                    implode(', ', self::PAYABLE_MODELS)
                )
            );
        }
    }
}
