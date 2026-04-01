<x-app-layout>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Licencias MikPoS</h1>
            <p class="text-gray-500 mt-1">Control de activaciones, ciclos de facturación y promociones.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('licenses.create') }}" class="inline-flex items-center px-4 py-2 bg-accent border border-transparent rounded-xl font-bold text-white hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150 shadow-sm shadow-accent/20">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Nueva Licencia
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
        <form action="{{ route('licenses.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="status" id="status" class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activas</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Vencidas</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Canceladas</option>
                </select>
            </div>
            <div>
                <label for="is_free_promotion" class="block text-sm font-medium text-gray-700 mb-1">Promoción Gratuita</label>
                <select name="is_free_promotion" id="is_free_promotion" class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                    <option value="">Todas</option>
                    <option value="1" {{ request('is_free_promotion') === '1' ? 'selected' : '' }}>Solo Gratuitas (5ta)</option>
                    <option value="0" {{ request('is_free_promotion') === '0' ? 'selected' : '' }}>Solo Pagas</option>
                </select>
            </div>
            <div class="md:col-span-1 flex items-end">
                <button type="submit" class="w-full bg-primary text-white px-4 py-2 rounded-xl font-medium hover:bg-primary/90 transition-colors">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan / Ciclo</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Próximo Pago</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Promoción</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($licenses as $license)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-lg bg-accent/10 text-accent flex items-center justify-center font-bold mr-3 text-xs">
                                        {{ substr($license->client->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ $license->client->name }}</div>
                                        <div class="text-[10px] text-gray-400 uppercase tracking-widest">{{ $license->license_key ?? 'KEY-PENDIENTE' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-800 font-medium">{{ $license->billing_cycle->value ?? $license->billing_cycle }}</div>
                                <div class="text-xs text-gray-500">${{ number_format($license->monthly_rate, 2) }} / mes</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($license->status === 'active')
                                    <div class="text-sm text-gray-800 font-medium">
                                        {{ $license->next_billing_at ? $license->next_billing_at->format('d/m/Y') : 'N/A' }}
                                    </div>
                                    <div class="text-[10px] text-emerald-600 font-bold uppercase">Activa</div>
                                @else
                                    <div class="text-sm text-gray-400 font-medium">--</div>
                                    <div class="text-[10px] text-red-400 font-bold uppercase">Inactiva</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($license->is_free_promotion)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        Gratis (5ta)
                                    </span>
                                @else
                                    <span class="text-gray-300 text-[10px] font-medium uppercase italic">Estándar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('licenses.show', $license) }}" class="text-gray-400 hover:text-primary transition-colors">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('licenses.edit', $license) }}" class="text-gray-400 hover:text-accent transition-colors">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">
                                No se encontraron licencias activas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($licenses->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $licenses->links() }}
            </div>
        @endif
    </div>
</x-app-layout>