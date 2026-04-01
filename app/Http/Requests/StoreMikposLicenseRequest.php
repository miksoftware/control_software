<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\BillingCycle;
use App\Enums\LicenseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMikposLicenseRequest extends FormRequest
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
            'client_id'        => ['required', 'integer', 'exists:clients,id'],
            'billing_cycle'    => ['required', Rule::enum(BillingCycle::class)],
            'monthly_rate'     => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'installation_fee' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'status'           => ['nullable', Rule::enum(LicenseStatus::class)],
            'activated_at'     => ['nullable', 'date'],
            'next_billing_at'  => ['nullable', 'date', 'after_or_equal:activated_at'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.required'     => 'Debe seleccionar un cliente.',
            'client_id.exists'       => 'El cliente seleccionado no existe.',
            'billing_cycle.required' => 'El ciclo de facturación es obligatorio.',
            'billing_cycle.enum'     => 'El ciclo de facturación no es válido.',
            'monthly_rate.min'       => 'La tarifa mensual no puede ser negativa.',
            'installation_fee.min'   => 'El costo de instalación no puede ser negativo.',
        ];
    }
}
