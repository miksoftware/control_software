<x-app-layout>
    @section('title', 'Detalle de Pago')

    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <a href="{{ route('payments.index') }}" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
            <h2 class="font-bold text-xl text-gray-800">Detalle de Pago #{{ $payment->id }}</h2>
        </div>
    </x-slot>

    <div class="max-w-lg">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-center mb-6">
                <p class="text-4xl font-bold text-emerald-600">${{ number_format((float)$payment->amount, 0, ',', '.') }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $payment->paid_at->format('d/m/Y') }}</p>
            </div>

            <dl class="space-y-3 divide-y divide-gray-100">
                <div class="flex justify-between pt-3">
                    <dt class="text-sm text-gray-500">Método</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $payment->payment_method->label() }}</dd>
                </div>
                <div class="flex justify-between pt-3">
                    <dt class="text-sm text-gray-500">Referencia</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $payment->reference ?: '—' }}</dd>
                </div>
                <div class="flex justify-between pt-3">
                    <dt class="text-sm text-gray-500">Cliente</dt>
                    <dd class="text-sm font-medium">
                        @if($payment->client)
                            <a href="{{ route('clients.show', $payment->client) }}" class="text-indigo-600 hover:text-indigo-800">{{ $payment->client->name }}</a>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between pt-3">
                    <dt class="text-sm text-gray-500">Categoría</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ ucfirst($payment->category) }}</dd>
                </div>
                @if($payment->notes)
                <div class="flex justify-between pt-3">
                    <dt class="text-sm text-gray-500">Notas</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $payment->notes }}</dd>
                </div>
                @endif
                <div class="flex justify-between pt-3">
                    <dt class="text-sm text-gray-500">Registrado</dt>
                    <dd class="text-sm text-gray-500">{{ $payment->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex gap-3">
                <a href="{{ route('payments.index') }}" class="flex-1 text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition-colors">Volver</a>
                <form method="POST" action="{{ route('payments.destroy', $payment) }}" onsubmit="return confirm('¿Eliminar este pago?')" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition-colors">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
