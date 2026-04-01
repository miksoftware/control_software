<x-app-layout>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Pagos y Abonos</h1>
            <p class="text-gray-500 mt-1">Registro histórico de todos los ingresos del sistema.</p>
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
                                <div class="text-[10px] text-gray-400 uppercase tracking-widest">{{ class_basename($payment->payable_type) }} #{{ $payment->payable_id }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-emerald-600 font-bold">${{ number_format($payment->amount, 2) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500 italic">
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
</x-app-layout>