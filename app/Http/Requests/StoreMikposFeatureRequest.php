<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMikposFeatureRequest extends FormRequest
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
            'client_id'             => ['required', 'integer', 'exists:clients,id'],
            'mikpos_license_id'     => ['nullable', 'integer', 'exists:mikpos_licenses,id'],
            'title'                 => ['required', 'string', 'max:255'],
            'description'           => ['nullable', 'string', 'max:5000'],
            'total_cost'            => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'status'                => ['nullable', Rule::enum(ProjectStatus::class)],
            'estimated_delivery_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.required'         => 'Debe seleccionar un cliente.',
            'client_id.exists'           => 'El cliente seleccionado no existe.',
            'mikpos_license_id.exists'   => 'La licencia MikPoS seleccionada no existe.',
            'title.required'             => 'El título de la mejora es obligatorio.',
            'total_cost.required'        => 'El costo total es obligatorio.',
            'total_cost.min'             => 'El costo total no puede ser negativo.',
        ];
    }
}
