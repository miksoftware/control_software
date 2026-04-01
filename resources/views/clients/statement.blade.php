<x-app-layout>
    <div class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between">
        <div>
            <a href="{{ route('clients.show', $client) }}" class="text-accent hover:text-accent/80 flex items-center text-sm font-medium mb-2 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al perfil
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Estado de Cuenta</h1>
            <p class="text-gray-500 mt-1">Resumen financiero detallado para <strong>{{ $client->name }}</strong>.</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="{{ route('payments.create', ['client_id' => $client->id]) }}" class="inline-flex items-center px-4 py-2 bg-accent border border-transparent rounded-xl font-bold text-white hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150 shadow-sm shadow-accent/20">
                Registrar Abono
            </a>
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150 shadow-sm">
                Imprimir Extracto
            </button>
        </div>
    </div>

    <!-- Totals Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Deuda Acumulada</p>
            <p class="text-3xl font-bold text-gray-900">${{ number_format($client->total_global_debt, 2) }}</p>
            <div class="mt-2 text-[10px] text-gray-400">Proyectos (${{ number_format($client->total_custom_projects_debt, 2) }}) + Mejoras (${{ number_format($client->total_features_debt, 2) }})</div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-emerald-500">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Abonos Realizados</p>
            <p class="text-3xl font-bold text-emerald-600">${{ number_format($client->total_paid, 2) }}</p>
            <div class="mt-2 text-[10px] text-emerald-500 uppercase font-bold tracking-tighter">{{ $client->payments->count() }} pagos registrados</div>
        </div>

        <div class="bg-primary p-6 rounded-2xl shadow-lg text-white relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-xs font-bold text-white/60 uppercase tracking-wider mb-2">Saldo Pendiente a la Fecha</p>
                <p class="text-4xl font-extrabold text-accent">${{ number_format($client->global_pending_balance, 2) }}</p>
                <p class="mt-2 text-[10px] text-white/40 italic">Corte realizado el {{ now()->format('d/m/Y H:i') }}</p>
            </div>
            <svg class="absolute right-[-20px] bottom-[-20px] w-32 h-32 text-white/5 opacity-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8">
        <!-- Requerimientos Pendientes -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Detalle de Requerimientos (Deuda)
            </h3>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Tipo</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Concepto</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Estado</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($client->customProjects as $project)
                            <tr>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-accent/10 text-accent uppercase">Proyecto</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-800">{{ $project->name }}</div>
                                    <div class="text-[10px] text-gray-400">Iniciado: {{ $project->start_date?->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs text-gray-600 uppercase">{{ $project->status->label() }}</span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900">${{ number_format($project->contract_value, 2) }}</td>
                            </tr>
                        @endforeach
                        @foreach($client->mikposFeatures as $feature)
                            <tr>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-primary/10 text-primary uppercase">Mejora</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-800">{{ $feature->title }}</div>
                                    <div class="text-[10px] text-gray-400">Solicitada: {{ $feature->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs text-gray-600 uppercase">{{ $feature->status->label() }}</span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900">${{ number_format($feature->total_cost, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Subtotal Deuda</td>
                            <td class="px-6 py-4 text-right font-extrabold text-gray-900">${{ number_format($client->total_global_debt, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Historial de Abonos -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Historial de Abonos (Crédito)
            </h3>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-emerald-50 border-b border-emerald-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-emerald-800 uppercase tracking-widest">Fecha</th>
                            <th class="px-6 py-4 text-xs font-bold text-emerald-800 uppercase tracking-widest">Referencia / Categoría</th>
                            <th class="px-6 py-4 text-xs font-bold text-emerald-800 uppercase tracking-widest">Método</th>
                            <th class="px-6 py-4 text-xs font-bold text-emerald-800 uppercase tracking-widest text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($client->payments as $payment)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $payment->paid_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-800">{{ $payment->reference ?? 'Abono a cuenta' }}</div>
                                    <div class="text-[10px] text-gray-400 uppercase font-bold">{{ $payment->category }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">{{ $payment->payment_method->label() }}</td>
                                <td class="px-6 py-4 text-right font-bold text-emerald-600">+ ${{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">No se han registrado abonos para este cliente.</td>
                            </tr>
                        @endforelse
                        <tr class="bg-emerald-50">
                            <td colspan="3" class="px-6 py-4 text-right text-xs font-bold text-emerald-800 uppercase">Total Pagado</td>
                            <td class="px-6 py-4 text-right font-extrabold text-emerald-700">${{ number_format($client->total_paid, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
