<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Models\CustomProject;
use App\Models\MikposFeature;
use App\Models\MikposLicense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'payable_type'   => [
                'required',
                'string',
                Rule::in([
                    MikposLicense::class,
                    MikposFeature::class,
                    CustomProject::class,
                    // También aceptar aliases cortos
                    'license',
                    'feature',
                    'project',
                ]),
            ],
            'payable_id'     => ['required', 'integer', 'min:1'],
            'amount'         => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference'      => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:2000'],
            'paid_at'        => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payable_type.required' => 'Debe especificar el tipo de entidad a la que se le registra el pago.',
            'payable_type.in'       => 'El tipo de entidad no es válido. Use: license, feature o project.',
            'payable_id.required'   => 'Debe especificar el ID de la entidad.',
            'payable_id.min'        => 'El ID de la entidad debe ser mayor a 0.',
            'amount.required'       => 'El monto del pago es obligatorio.',
            'amount.gt'             => 'El monto debe ser mayor a cero.',
            'payment_method.required' => 'El método de pago es obligatorio.',
            'payment_method.enum'    => 'El método de pago no es válido.',
            'paid_at.before_or_equal' => 'La fecha de pago no puede ser futura.',
        ];
    }

    /**
     * Resuelve el tipo de modelo completo a partir del alias corto.
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    public function resolvedPayableType(): string
    {
        return match ($this->validated('payable_type')) {
            'license', MikposLicense::class => MikposLicense::class,
            'feature', MikposFeature::class => MikposFeature::class,
            'project', CustomProject::class => CustomProject::class,
        };
    }
}
