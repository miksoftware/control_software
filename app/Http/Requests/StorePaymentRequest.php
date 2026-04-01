<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
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
            'client_id'      => ['required', 'integer', 'exists:clients,id'],
            'category'       => ['required', 'string', Rule::in(['global', 'projects', 'features'])],
            'amount'         => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference'      => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:2000'],
            'paid_at'           => ['nullable', 'date', 'before_or_equal:today'],
            'custom_project_id' => ['nullable', 'integer', 'exists:custom_projects,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.required'      => 'Debe seleccionar un cliente.',
            'client_id.exists'        => 'El cliente seleccionado no existe.',
            'category.required'       => 'La categoría del pago es obligatoria.',
            'category.in'             => 'La categoría debe ser: global, projects o features.',
            'amount.required'         => 'El monto del pago es obligatorio.',
            'amount.gt'               => 'El monto debe ser mayor a cero.',
            'payment_method.required' => 'El método de pago es obligatorio.',
            'payment_method.enum'     => 'El método de pago no es válido.',
            'paid_at.before_or_equal' => 'La fecha de pago no puede ser futura.',
        ];
    }
}
