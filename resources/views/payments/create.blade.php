<x-app-layout>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Registrar Abono</h1>
        <p class="text-gray-500 mt-1">Ingresa un nuevo pago a la cuenta corriente del cliente.</p>
    </div>

    <div class="max-w-3xl">
        <form action="{{ route('payments.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @csrf
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label for="client_id" class="block text-sm font-semibold text-gray-700 mb-1">Cliente</label>
                        <select name="client_id" id="client_id" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                            <option value="">Selecciona un cliente</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ (old('client_id') ?? ($selectedClient?->id)) == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }} (Saldo: ${{ number_format($client->global_pending_balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('client_id')" class="mt-1" />
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-semibold text-gray-700 mb-1">Destino del Abono</label>
                        <select name="category" id="category" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                            <option value="global" {{ old('category', $category) === 'global' ? 'selected' : '' }}>Cuenta Global (Recomendado)</option>
                            <option value="projects" {{ old('category', $category) === 'projects' ? 'selected' : '' }}>Solo Proyectos a Medida</option>
                            <option value="features" {{ old('category', $category) === 'features' ? 'selected' : '' }}>Solo Mejoras MikPoS</option>
                        </select>
                        <x-input-error :messages="$errors->get('category')" class="mt-1" />
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-semibold text-gray-700 mb-1">Monto del Abono ($)</label>
                        <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount') }}" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm" placeholder="0.00">
                        <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-semibold text-gray-700 mb-1">Método de Pago</label>
                        <select name="payment_method" id="payment_method" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->value }}" {{ old('payment_method') == $method->value ? 'selected' : '' }}>
                                    {{ $method->label() }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('payment_method')" class="mt-1" />
                    </div>

                    <div>
                        <label for="paid_at" class="block text-sm font-semibold text-gray-700 mb-1">Fecha del Pago</label>
                        <input type="date" name="paid_at" id="paid_at" value="{{ old('paid_at', now()->format('Y-m-d')) }}" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                        <x-input-error :messages="$errors->get('paid_at')" class="mt-1" />
                    </div>

                    <div class="col-span-2">
                        <label for="reference" class="block text-sm font-semibold text-gray-700 mb-1">Referencia / No. Comprobante</label>
                        <input type="text" name="reference" id="reference" value="{{ old('reference') }}" class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm" placeholder="Ej. Transf. Bancaria #12345">
                        <x-input-error :messages="$errors->get('reference')" class="mt-1" />
                    </div>

                    <div class="col-span-2">
                        <label for="notes" class="block text-sm font-semibold text-gray-700 mb-1">Notas Internas</label>
                        <textarea name="notes" id="notes" rows="3" class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">{{ old('notes') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-4">
                <a href="{{ url()->previous() }}" class="text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">Cancelar</a>
                <button type="submit" class="bg-accent text-white px-6 py-2 rounded-xl font-bold hover:bg-accent/90 transition-colors shadow-sm shadow-accent/20">
                    Confirmar Abono
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
