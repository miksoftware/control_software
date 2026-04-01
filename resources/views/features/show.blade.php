<x-app-layout>
    <div class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between">
        <div>
            <a href="{{ route('features.index') }}" class="text-accent hover:text-accent/80 flex items-center text-sm font-medium mb-2 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al listado
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ $feature->title }}</h1>
            <p class="text-gray-500 mt-1">Cliente: <a href="{{ route('clients.show', $feature->client) }}" class="text-primary hover:underline font-medium">{{ $feature->client->name }}</a></p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="{{ route('clients.statement', $feature->client) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150 shadow-sm">
                Estado de Cuenta
            </a>
            <a href="{{ route('features.edit', $feature) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150 shadow-sm">
                Editar Mejora
            </a>
            <a href="{{ route('payments.create', ['client_id' => $feature->client_id, 'category' => 'features']) }}" class="inline-flex items-center px-4 py-2 bg-accent border border-transparent rounded-xl font-bold text-white hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150 shadow-sm shadow-accent/20">
                Registrar Pago
            </a>
        </div>
    </div>

    <!-- Financial Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Costo de Mejora</span>
                <div class="p-2 bg-gray-50 rounded-lg text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900">${{ number_format($feature->total_cost, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Valor total de la mejora</div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Abonado a Mejoras</span>
                <div class="p-2 bg-emerald-50 rounded-lg text-emerald-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-emerald-600">${{ number_format($feature->client->payments()->where('category', 'features')->sum('amount'), 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Total abonos en categoría</div>
        </div>

        <div class="bg-primary p-6 rounded-2xl shadow-sm text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-white/50 uppercase tracking-wider">Saldo Global Cliente</span>
                <div class="p-2 bg-white/10 rounded-lg text-accent">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-accent">
                ${{ number_format($feature->client->global_pending_balance, 2) }}
            </div>
            <div class="text-xs text-white/40 mt-1">Deuda total unificada</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Detalles de la Mejora</h3>
                <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed">
                    {{ $feature->description ?? 'Sin descripción proporcionada.' }}
                </div>
                
                <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-6 pt-8 border-t border-gray-50">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Estado</p>
                        <p class="text-sm font-bold text-primary uppercase">{{ $feature->status->label() }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Entrega Est.</p>
                        <p class="text-sm font-medium text-gray-800">{{ $feature->estimated_delivery_at ? $feature->estimated_delivery_at->format('d/m/Y') : 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Finalizado</p>
                        <p class="text-sm font-medium text-gray-800">{{ $feature->completed_at ? $feature->completed_at->format('d/m/Y') : 'En curso' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Licencia Asoc.</p>
                        <p class="text-sm font-medium text-gray-800">{{ $feature->mikposLicense ? 'Activa' : 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">Historial de Pagos</h3>
                </div>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-8 py-3 font-bold text-gray-400 uppercase text-[10px]">Fecha</th>
                            <th class="px-8 py-3 font-bold text-gray-400 uppercase text-[10px]">Referencia</th>
                            <th class="px-8 py-3 font-bold text-gray-400 uppercase text-[10px] text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($feature->client->payments()->where('category', 'features')->orderByDesc('paid_at')->limit(5)->get() as $payment)
                            <tr>
                                <td class="px-8 py-4 text-gray-600">{{ $payment->paid_at->format('d/m/Y') }}</td>
                                <td class="px-8 py-4">
                                    <div class="font-medium text-gray-900">{{ $payment->reference ?? 'Pago de mejora' }}</div>
                                    <div class="text-[10px] text-gray-400 uppercase">{{ $payment->payment_method->label() }}</div>
                                </td>
                                <td class="px-8 py-4 text-right font-bold text-emerald-600">${{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-8 py-12 text-center text-gray-400 italic">No hay pagos recientes en esta categoría.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="lg:col-span-1">
            <div class="bg-primary p-6 rounded-2xl shadow-sm text-white sticky top-8">
                <h3 class="text-lg font-bold mb-4">Acciones Rápidas</h3>
                <div class="space-y-3">
                    <a href="{{ route('payments.create', ['client_id' => $feature->client_id, 'category' => 'features']) }}" class="w-full flex items-center justify-center py-3 px-4 bg-accent hover:bg-accent/90 rounded-xl font-bold transition-colors">
                        Registrar Abono
                    </a>
                    <a href="{{ route('clients.statement', $feature->client_id) }}" class="w-full flex items-center justify-center py-3 px-4 bg-white/10 hover:bg-white/20 rounded-xl font-bold transition-colors">
                        Estado de Cuenta
                    </a>
                </div>
                <div class="mt-8 pt-8 border-t border-white/10">
                    <p class="text-xs text-white/50 mb-4">Información del Cliente</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-accent flex items-center justify-center font-bold mr-3 text-sm">
                            {{ substr($feature->client->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold">{{ $feature->client->name }}</p>
                            <p class="text-[10px] text-white/50 uppercase">{{ $feature->client->email }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Pago (Eliminado por refactorización a Cuenta Corriente) -->
</x-app-layout>