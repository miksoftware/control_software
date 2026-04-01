<x-app-layout>
    <div class="mb-8">
        <a href="{{ route('licenses.show', $license) }}" class="text-accent hover:text-accent/80 flex items-center text-sm font-medium mb-2 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver a la licencia
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Editar Licencia</h1>
        <p class="text-gray-500 mt-1">Gestionando licencia de <strong>{{ $license->client->name }}</strong>.</p>
    </div>

    <div class="max-w-3xl">
        <form action="{{ route('licenses.update', $license) }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @csrf
            @method('PUT')
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-400 mb-1">Cliente (No editable)</label>
                        <div class="px-4 py-3 bg-gray-100 rounded-xl text-gray-600 text-sm font-medium">
                            {{ $license->client->name }}
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label for="site_url" class="block text-sm font-semibold text-gray-700 mb-1">URL del Sitio Web</label>
                        <input type="url" name="site_url" id="site_url" value="{{ old('site_url', $license->site_url) }}" placeholder="https://ejemplo.com" class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                        <x-input-error :messages="$errors->get('site_url')" class="mt-1" />
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">Estado de Licencia</label>
                        <select name="status" id="status" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                            @foreach(\App\Enums\LicenseStatus::cases() as $status)
                                <option value="{{ $status->value }}" {{ old('status', $license->status->value) == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>

                    <div>
                        <label for="billing_cycle" class="block text-sm font-semibold text-gray-700 mb-1">Ciclo de Facturación</label>
                        <select name="billing_cycle" id="billing_cycle" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                            @foreach($billingCycles as $cycle)
                                <option value="{{ $cycle->value }}" {{ old('billing_cycle', $license->billing_cycle->value ?? $license->billing_cycle) == $cycle->value ? 'selected' : '' }}>
                                    {{ $cycle->label() }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('billing_cycle')" class="mt-1" />
                    </div>

                    <div>
                        <label for="monthly_rate" class="block text-sm font-semibold text-gray-700 mb-1">Tarifa Mensual ($)</label>
                        <input type="number" step="0.01" name="monthly_rate" id="monthly_rate" value="{{ old('monthly_rate', $license->monthly_rate) }}" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                        <x-input-error :messages="$errors->get('monthly_rate')" class="mt-1" />
                    </div>

                    <div>
                        <label for="next_billing_at" class="block text-sm font-semibold text-gray-700 mb-1">Próxima Facturación</label>
                        <input type="date" name="next_billing_at" id="next_billing_at" value="{{ old('next_billing_at', $license->next_billing_at ? $license->next_billing_at->format('Y-m-d') : '') }}" class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                        <x-input-error :messages="$errors->get('next_billing_at')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-4">
                <a href="{{ route('licenses.show', $license) }}" class="text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">Cancelar</a>
                <button type="submit" class="bg-accent text-white px-6 py-2 rounded-xl font-bold hover:bg-accent/90 transition-colors shadow-sm shadow-accent/20">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</x-app-layout>