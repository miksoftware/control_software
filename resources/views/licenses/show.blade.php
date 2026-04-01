<x-app-layout>
    @section('title', $license->license_key)

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <a href="{{ route('licenses.index') }}" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
                <h2 class="font-bold text-xl text-gray-800 font-mono">{{ $license->license_key }}</h2>
                <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $license->status->value === 'active' ? 'bg-emerald-100 text-emerald-700' : ($license->status->value === 'suspended' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">{{ $license->status->label() }}</span>
                @if($license->is_free_promotion)
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">🎁 Gratis</span>
                @endif
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('licenses.edit', $license) }}" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">Editar</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- License Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Detalles de Licencia</h3>
            <dl class="space-y-2">
                <div><dt class="text-xs text-gray-400">Cliente</dt><dd class="text-sm font-medium"><a href="{{ route('clients.show', $license->client) }}" class="text-indigo-600 hover:text-indigo-800">{{ $license->client->name }}</a></dd></div>
                <div><dt class="text-xs text-gray-400">Tipo Cliente</dt><dd class="text-sm font-medium">{{ $license->client->client_type->label() }}</dd></div>
                <div><dt class="text-xs text-gray-400">Ciclo</dt><dd class="text-sm font-medium">{{ $license->billing_cycle->label() }}</dd></div>
                <div><dt class="text-xs text-gray-400">Tarifa Mensual</dt><dd class="text-sm font-medium">${{ number_format((float)$license->monthly_rate, 0, ',', '.') }}</dd></div>
                <div><dt class="text-xs text-gray-400">Costo Instalación</dt><dd class="text-sm font-medium">${{ number_format((float)$license->installation_fee, 0, ',', '.') }}</dd></div>
                <div><dt class="text-xs text-gray-400">Valor Ciclo</dt><dd class="text-sm font-bold text-gray-900">${{ number_format($license->cycle_amount, 0, ',', '.') }}</dd></div>
                <div><dt class="text-xs text-gray-400">Activada</dt><dd class="text-sm font-medium">{{ $license->activated_at?->format('d/m/Y') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Próx. Facturación</dt><dd class="text-sm font-medium">{{ $license->next_billing_at?->format('d/m/Y') ?? '—' }}</dd></div>
            </dl>
        </div>

        <!-- Financial Summary -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Resumen de Licencia</h3>
            <div class="space-y-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-400">Valor Total por Ciclo</p>
                    <p class="text-3xl font-bold text-blue-600">${{ number_format((float)$license->installation_fee + $license->cycle_amount, 0, ',', '.') }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="p-3 bg-blue-50 rounded-lg">
                        <p class="text-xs text-blue-500">Tarifa Ciclo</p>
                        <p class="text-lg font-bold text-blue-700">${{ number_format($license->cycle_amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 bg-emerald-50 rounded-lg">
                        <p class="text-xs text-emerald-500">Instalación</p>
                        <p class="text-lg font-bold text-emerald-700">${{ number_format((float)$license->installation_fee, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reseller Summary (if applicable) -->
        @if($resellerSummary)
        <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl shadow-sm border border-purple-200 p-6">
            <h3 class="text-sm font-semibold text-purple-600 uppercase mb-3">📊 Resumen Revendedor</h3>
            <dl class="space-y-2">
                <div><dt class="text-xs text-purple-400">Total Licencias</dt><dd class="text-sm font-bold text-purple-700">{{ $resellerSummary['total_licenses'] }}</dd></div>
                <div><dt class="text-xs text-purple-400">Licencias Pagadas</dt><dd class="text-sm font-bold text-purple-700">{{ $resellerSummary['paid_licenses'] }}</dd></div>
                <div><dt class="text-xs text-purple-400">Licencias Gratis</dt><dd class="text-sm font-bold text-green-600">{{ $resellerSummary['free_licenses'] }}</dd></div>
                <div class="pt-2 border-t border-purple-200">
                    <dt class="text-xs text-purple-400">Próxima Licencia</dt>
                    <dd class="text-sm font-bold {{ $resellerSummary['next_license_is_free'] ? 'text-green-600' : 'text-purple-700' }}">
                        @if($resellerSummary['next_license_is_free'])
                            🎁 ¡SERÁ GRATIS!
                        @else
                            Faltan {{ $resellerSummary['remaining_for_next_free'] }} para la siguiente gratis
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
        @endif
    </div>

    <!-- Features List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Mejoras/Cambios Asociados ({{ $license->features->count() }})</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($license->features as $feature)
                <div class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                    <div>
                        <p class="font-medium text-sm text-gray-800">{{ $feature->title }}</p>
                        <p class="text-xs text-gray-500">{{ $feature->status->label() }} · ${{ number_format((float)$feature->total_cost, 0, ',', '.') }}</p>
                    </div>
                    <a href="{{ route('features.show', $feature) }}" class="text-gray-400 hover:text-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            @empty
                <div class="px-6 py-6 text-center text-gray-400">Sin mejoras asociadas a esta licencia.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
