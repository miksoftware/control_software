<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ClientType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
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
        $clientId = $this->route('client')?->id ?? $this->route('client');

        return [
            'name'         => ['sometimes', 'required', 'string', 'max:255'],
            'email'        => ['sometimes', 'required', 'email', 'max:255', Rule::unique('clients', 'email')->ignore($clientId)],
            'phone'        => ['nullable', 'string', 'max:20'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:500'],
            'client_type'  => ['sometimes', 'required', Rule::enum(ClientType::class)],
            'notes'        => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'        => 'El nombre del cliente es obligatorio.',
            'email.required'       => 'El correo electrónico es obligatorio.',
            'email.email'          => 'Debe ingresar un correo electrónico válido.',
            'email.unique'         => 'Este correo electrónico ya está registrado por otro cliente.',
            'client_type.required' => 'El tipo de cliente es obligatorio.',
            'client_type.enum'     => 'El tipo de cliente debe ser "final" o "reseller".',
        ];
    }
}
