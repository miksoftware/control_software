<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomProjectRequest extends FormRequest
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
            'client_id'          => ['required', 'integer', 'exists:clients,id'],
            'name'               => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string', 'max:5000'],
            'contract_value'     => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'status'             => ['nullable', Rule::enum(ProjectStatus::class)],
            'start_date'         => ['nullable', 'date'],
            'estimated_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.required'              => 'Debe seleccionar un cliente.',
            'client_id.exists'                => 'El cliente seleccionado no existe.',
            'name.required'                   => 'El nombre del proyecto es obligatorio.',
            'contract_value.required'         => 'El valor del contrato es obligatorio.',
            'contract_value.min'              => 'El valor del contrato no puede ser negativo.',
            'estimated_end_date.after_or_equal' => 'La fecha estimada de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }
}
