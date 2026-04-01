<x-app-layout>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Pagos y Abonos</h1>
            <p class="text-gray-500 mt-1">Registro histórico de todos los ingresos del sistema.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <button onclick="document.getElementById('paymentModal').classList.remove('hidden')" class="inline-flex items-center px-5 py-2.5 bg-accent border border-transparent rounded-xl font-bold text-white hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150 shadow-sm shadow-accent/20">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Registrar Pago o Abono
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Concepto / Referencia</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Método</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50/50 transition-colors text-sm">
                            <td class="px-6 py-4 text-gray-600">
                                {{ $payment->paid_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $payment->client->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-800">{{ $payment->reference ?? 'Pago registrado' }}</div>
                                <div class="text-[10px] text-gray-400 uppercase tracking-widest">
                                    {{ ucfirst($payment->category) }}
                                    @if($payment->customProject)
                                        &mdash; {{ $payment->customProject->name }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-[10px] text-gray-400 uppercase">{{ $payment->payment_method->label() }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-emerald-600 font-bold">${{ number_format($payment->amount, 2) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">
                                No hay pagos registrados en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Registrar Pago o Abono -->
    <div id="paymentModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true" x-data="paymentModalData()" x-init="init()">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="document.getElementById('paymentModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-auto">
                <form method="POST" action="{{ route('payments.store') }}">
                    @csrf
                    <div class="px-8 py-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Registrar Pago o Abono</h3>
                                <p class="text-sm text-gray-500 mt-1">Selecciona un cliente y el destino del pago.</p>
                            </div>
                            <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="px-8 py-6 space-y-5">
                        <!-- Cliente -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                            <select name="client_id" required x-model="selectedClientId" @change="onClientChange()" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                <option value="">Selecciona un cliente</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Destino -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Destino del Abono *</label>
                            <select name="category" required x-model="selectedCategory" @change="onCategoryChange()" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                <option value="global">Cuenta Global (Recomendado)</option>
                                <option value="projects">Proyecto a Medida</option>
                                <option value="features">Mejoras MikPoS</option>
                            </select>
                        </div>

                        <!-- Proyecto específico (visible solo si category = projects) -->
                        <div x-show="selectedCategory === 'projects' && clientProjects.length > 0" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Proyecto Específico</label>
                            <select name="custom_project_id" x-model="selectedProjectId" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                <option value="">General (sin asignar a proyecto)</option>
                                <template x-for="proj in clientProjects" :key="proj.id">
                                    <option :value="proj.id" x-text="proj.name + ' ($' + Number(proj.contract_value).toFixed(2) + ')'"></option>
                                </template>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Selecciona un proyecto para asignar el pago directamente.</p>
                        </div>

                        <div x-show="selectedCategory === 'projects' && selectedClientId && clientProjects.length === 0" x-cloak>
                            <p class="text-sm text-amber-600 bg-amber-50 p-3 rounded-xl">Este cliente no tiene proyectos a medida registrados.</p>
                        </div>

                        <!-- Monto -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monto del Abono ($) *</label>
                            <input type="number" step="0.01" name="amount" required min="0.01" placeholder="0.00" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>

                        <!-- Método de Pago -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago *</label>
                            <select name="payment_method" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->value }}">{{ $method->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fecha -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha del Pago *</label>
                            <input type="date" name="paid_at" value="{{ now()->format('Y-m-d') }}" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>

                        <!-- Referencia -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Referencia / Comprobante</label>
                            <input type="text" name="reference" placeholder="Ej: Transf. #12345" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>

                        <!-- Notas -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                            <textarea name="notes" rows="2" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm"></textarea>
                        </div>
                    </div>
                    <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-3 rounded-b-2xl">
                        <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancelar</button>
                        <button type="submit" class="px-6 py-2 bg-accent text-white text-sm font-bold rounded-xl hover:bg-accent/90 transition-colors shadow-sm shadow-accent/20">Confirmar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function paymentModalData() {
            return {
                selectedClientId: '',
                selectedCategory: 'global',
                selectedProjectId: '',
                clientProjects: [],
                allProjects: @json($projects->groupBy('client_id')),
                init() {},
                onClientChange() {
                    this.selectedProjectId = '';
                    this.clientProjects = this.allProjects[this.selectedClientId] || [];
                },
                onCategoryChange() {
                    if (this.selectedCategory !== 'projects') {
                        this.selectedProjectId = '';
                    }
                }
            }
        }
    </script>

    @if($errors->any())
    <script>document.addEventListener('DOMContentLoaded', () => document.getElementById('paymentModal').classList.remove('hidden'));</script>
    @endif
</x-app-layout>