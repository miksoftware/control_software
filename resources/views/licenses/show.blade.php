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
                <button onclick="document.getElementById('editLicenseModal').classList.remove('hidden')" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">Editar</button>
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
                <div><dt class="text-xs text-gray-400">URL Sitio Web</dt><dd class="text-sm font-medium">@if($license->site_url)<a href="{{ $license->site_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 break-all">{{ $license->site_url }}</a>@else <span class="text-gray-400">No registrada</span> @endif</dd></div>
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

    <!-- Modal: Editar Licencia -->
    <div id="editLicenseModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="document.getElementById('editLicenseModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-auto">
                <form method="POST" action="{{ route('licenses.update', $license) }}">
                    @csrf
                    @method('PUT')
                    <div class="px-8 py-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Editar Licencia</h3>
                                <p class="text-sm text-gray-500 mt-1">{{ $license->client->name }} &mdash; {{ $license->license_key }}</p>
                            </div>
                            <button type="button" onclick="document.getElementById('editLicenseModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="px-8 py-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL del Sitio Web</label>
                            <input type="url" name="site_url" value="{{ old('site_url', $license->site_url) }}" placeholder="https://ejemplo.com" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="status" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                    @foreach(\App\Enums\LicenseStatus::cases() as $status)
                                        <option value="{{ $status->value }}" {{ $license->status->value === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ciclo de Facturación</label>
                                <select name="billing_cycle" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                    @foreach($billingCycles as $cycle)
                                        <option value="{{ $cycle->value }}" {{ ($license->billing_cycle->value ?? $license->billing_cycle) === $cycle->value ? 'selected' : '' }}>{{ $cycle->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tarifa Mensual ($)</label>
                                <input type="number" step="0.01" name="monthly_rate" value="{{ old('monthly_rate', $license->monthly_rate) }}" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Próxima Facturación</label>
                                <input type="date" name="next_billing_at" value="{{ old('next_billing_at', $license->next_billing_at?->format('Y-m-d')) }}" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-3 rounded-b-2xl">
                        <button type="button" onclick="document.getElementById('editLicenseModal').classList.add('hidden')" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancelar</button>
                        <button type="submit" class="px-6 py-2 bg-accent text-white text-sm font-bold rounded-xl hover:bg-accent/90 transition-colors shadow-sm shadow-accent/20">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($errors->any())
    <script>document.addEventListener('DOMContentLoaded', () => document.getElementById('editLicenseModal').classList.remove('hidden'));</script>
    @endif
</x-app-layout>
