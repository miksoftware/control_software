<x-app-layout>
    <div class="mb-8">
        <a href="{{ route('licenses.index') }}" class="text-accent hover:text-accent/80 flex items-center text-sm font-medium mb-2 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver al listado
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Nueva Licencia MikPoS</h1>
        <p class="text-gray-500 mt-1">Registra una nueva instalación de MikPoS para un cliente.</p>
    </div>

    <div class="max-w-3xl">
        <form action="{{ route('licenses.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @csrf
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label for="client_id" class="block text-sm font-semibold text-gray-700 mb-1">Cliente</label>
                        <select name="client_id" id="client_id" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                            <option value="">Selecciona un cliente</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ (old('client_id') ?? request('client_id')) == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }} ({{ $client->client_type->label() }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('client_id')" class="mt-1" />
                    </div>

                    <div class="col-span-2">
                        <label for="site_url" class="block text-sm font-semibold text-gray-700 mb-1">URL del Sitio Web</label>
                        <input type="url" name="site_url" id="site_url" value="{{ old('site_url') }}" placeholder="https://ejemplo.com" class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                        <x-input-error :messages="$errors->get('site_url')" class="mt-1" />
                    </div>

                    <div>
                        <label for="billing_cycle" class="block text-sm font-semibold text-gray-700 mb-1">Ciclo de Facturación</label>
                        <select name="billing_cycle" id="billing_cycle" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                            @foreach($billingCycles as $cycle)
                                <option value="{{ $cycle->value }}" {{ old('billing_cycle') == $cycle->value ? 'selected' : '' }}>
                                    {{ $cycle->label() }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('billing_cycle')" class="mt-1" />
                    </div>

                    <div>
                        <label for="monthly_rate" class="block text-sm font-semibold text-gray-700 mb-1">Tarifa Mensual ($)</label>
                        <input type="number" step="0.01" name="monthly_rate" id="monthly_rate" value="{{ old('monthly_rate', 0.00) }}" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                        <x-input-error :messages="$errors->get('monthly_rate')" class="mt-1" />
                    </div>

                    <div>
                        <label for="next_billing_at" class="block text-sm font-semibold text-gray-700 mb-1">Fecha Primer Pago</label>
                        <input type="date" name="next_billing_at" id="next_billing_at" value="{{ old('next_billing_at', now()->format('Y-m-d')) }}" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                        <x-input-error :messages="$errors->get('next_billing_at')" class="mt-1" />
                    </div>

                    <div class="col-span-2">
                        <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm text-blue-700">
                                <strong>Nota para Revendedores:</strong> Si este es el 5to cliente del revendedor, el sistema marcará automáticamente esta licencia como <strong>Gratuita</strong> según la política de MIKSOFTWARE.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-4">
                <a href="{{ route('licenses.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">Cancelar</a>
                <button type="submit" class="bg-accent text-white px-6 py-2 rounded-xl font-bold hover:bg-accent/90 transition-colors shadow-sm shadow-accent/20">
                    Activar Licencia
                </button>
            </div>
        </form>
    </div>
</x-app-layout>