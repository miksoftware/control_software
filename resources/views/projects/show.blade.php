<x-app-layout>
    <div class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between">
        <div>
            <a href="{{ route('projects.index') }}" class="text-accent hover:text-accent/80 flex items-center text-sm font-medium mb-2 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al listado
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ $project->name }}</h1>
            <p class="text-gray-500 mt-1">Cliente: <a href="{{ route('clients.show', $project->client) }}" class="text-primary hover:underline font-medium">{{ $project->client->name }}</a></p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="{{ route('clients.statement', $project->client) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150 shadow-sm">
                Estado de Cuenta
            </a>
            <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150 shadow-sm">
                Editar Proyecto
            </a>
            <button onclick="document.getElementById('projectPaymentModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-accent border border-transparent rounded-xl font-bold text-white hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150 shadow-sm shadow-accent/20">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Registrar Pago
            </button>
        </div>
    </div>

    @php
        $projectTotalPaid = $project->payments->sum('amount');
        $projectPending = max(0, (float) $project->contract_value - $projectTotalPaid);
    @endphp

    <!-- Financial Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Valor del Proyecto</span>
                <div class="p-2 bg-gray-50 rounded-lg text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900">${{ number_format($project->contract_value, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Presupuesto pactado</div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Abonado a este Proyecto</span>
                <div class="p-2 bg-emerald-50 rounded-lg text-emerald-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-emerald-600">${{ number_format($projectTotalPaid, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $project->payments->count() }} pagos registrados</div>
        </div>

        <div class="bg-primary p-6 rounded-2xl shadow-sm text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-white/50 uppercase tracking-wider">Saldo Pendiente Proyecto</span>
                <div class="p-2 bg-white/10 rounded-lg text-accent">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-accent">
                ${{ number_format($projectPending, 2) }}
            </div>
            <div class="text-xs text-white/40 mt-1">
                @if($projectPending <= 0) Proyecto pagado al 100% @else Faltan por pagar @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Descripción del Proyecto</h3>
                <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed">
                    {{ $project->description ?? 'Sin descripción proporcionada.' }}
                </div>
                
                <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-6 pt-8 border-t border-gray-50">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Estado</p>
                        <p class="text-sm font-bold text-primary uppercase">{{ $project->status->label() }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Inicio</p>
                        <p class="text-sm font-medium text-gray-800">{{ $project->start_date ? $project->start_date->format('d/m/Y') : 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Entrega Est.</p>
                        <p class="text-sm font-medium text-gray-800">{{ $project->estimated_end_date ? $project->estimated_end_date->format('d/m/Y') : 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Finalizado</p>
                        <p class="text-sm font-medium text-gray-800">{{ $project->actual_end_date ? $project->actual_end_date->format('d/m/Y') : 'En curso' }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment History for this Project -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">Historial de Pagos de este Proyecto</h3>
                    <button onclick="document.getElementById('projectPaymentModal').classList.remove('hidden')" class="text-accent text-sm font-bold hover:underline">+ Registrar Pago</button>
                </div>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-8 py-3 font-bold text-gray-400 uppercase text-[10px]">Fecha</th>
                            <th class="px-8 py-3 font-bold text-gray-400 uppercase text-[10px]">Referencia</th>
                            <th class="px-8 py-3 font-bold text-gray-400 uppercase text-[10px]">Método</th>
                            <th class="px-8 py-3 font-bold text-gray-400 uppercase text-[10px] text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($project->payments->sortByDesc('paid_at') as $payment)
                            <tr>
                                <td class="px-8 py-4 text-gray-600">{{ $payment->paid_at->format('d/m/Y') }}</td>
                                <td class="px-8 py-4">
                                    <div class="font-medium text-gray-900">{{ $payment->reference ?? 'Pago de proyecto' }}</div>
                                </td>
                                <td class="px-8 py-4">
                                    <div class="text-[10px] text-gray-400 uppercase">{{ $payment->payment_method->label() }}</div>
                                </td>
                                <td class="px-8 py-4 text-right font-bold text-emerald-600">${{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center text-gray-400 italic">No hay pagos registrados para este proyecto.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-primary p-6 rounded-2xl shadow-sm text-white sticky top-8">
                <h3 class="text-lg font-bold mb-4">Acciones Rápidas</h3>
                <div class="space-y-3">
                    <button onclick="document.getElementById('projectPaymentModal').classList.remove('hidden')" class="w-full flex items-center justify-center py-3 px-4 bg-accent hover:bg-accent/90 rounded-xl font-bold transition-colors">
                        Registrar Abono
                    </button>
                    <a href="{{ route('clients.statement', $project->client_id) }}" class="w-full flex items-center justify-center py-3 px-4 bg-white/10 hover:bg-white/20 rounded-xl font-bold transition-colors">
                        Estado de Cuenta
                    </a>
                </div>
                <div class="mt-8 pt-8 border-t border-white/10">
                    <p class="text-xs text-white/50 mb-4">Información del Cliente</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-accent flex items-center justify-center font-bold mr-3 text-sm">
                            {{ substr($project->client->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold">{{ $project->client->name }}</p>
                            <p class="text-[10px] text-white/50 uppercase">{{ $project->client->email }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Registrar Pago para este Proyecto -->
    <div id="projectPaymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="document.getElementById('projectPaymentModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-auto">
                <form method="POST" action="{{ route('payments.store') }}">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $project->client_id }}">
                    <input type="hidden" name="category" value="projects">
                    <input type="hidden" name="custom_project_id" value="{{ $project->id }}">
                    <div class="px-8 py-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Registrar Pago</h3>
                                <p class="text-sm text-gray-500 mt-1">Proyecto: {{ $project->name }}</p>
                            </div>
                            <button type="button" onclick="document.getElementById('projectPaymentModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="px-8 py-6 space-y-5">
                        <div class="bg-gray-50 p-4 rounded-xl">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Valor del proyecto:</span>
                                <span class="font-bold">${{ number_format($project->contract_value, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-gray-500">Ya abonado:</span>
                                <span class="font-bold text-emerald-600">${{ number_format($projectTotalPaid, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm mt-1 pt-2 border-t border-gray-200">
                                <span class="text-gray-700 font-bold">Pendiente:</span>
                                <span class="font-bold text-accent">${{ number_format($projectPending, 2) }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monto del Abono ($) *</label>
                            <input type="number" step="0.01" name="amount" required min="0.01" max="{{ $projectPending }}" placeholder="0.00" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago *</label>
                            <select name="payment_method" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                @foreach(\App\Enums\PaymentMethod::cases() as $method)
                                    <option value="{{ $method->value }}">{{ $method->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha del Pago *</label>
                            <input type="date" name="paid_at" value="{{ now()->format('Y-m-d') }}" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Referencia / Comprobante</label>
                            <input type="text" name="reference" placeholder="Ej: Transf. #12345" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                            <textarea name="notes" rows="2" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm"></textarea>
                        </div>
                    </div>
                    <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-3 rounded-b-2xl">
                        <button type="button" onclick="document.getElementById('projectPaymentModal').classList.add('hidden')" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancelar</button>
                        <button type="submit" class="px-6 py-2 bg-accent text-white text-sm font-bold rounded-xl hover:bg-accent/90 transition-colors shadow-sm shadow-accent/20">Confirmar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($errors->any())
    <script>document.addEventListener('DOMContentLoaded', () => document.getElementById('projectPaymentModal').classList.remove('hidden'));</script>
    @endif
</x-app-layout>