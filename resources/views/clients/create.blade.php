<x-app-layout>
    <div class="mb-8">
        <a href="{{ route('clients.index') }}" class="text-accent hover:text-accent/80 flex items-center text-sm font-medium mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver al listado
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Nuevo Cliente</h1>
        <p class="text-gray-500 mt-1">Completa la información para registrar un nuevo cliente o revendedor.</p>
    </div>

    <div class="max-w-3xl">
        <form action="{{ route('clients.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @csrf
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nombre Completo / Empresa</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <label for="client_type" class="block text-sm font-semibold text-gray-700 mb-1">Tipo de Cliente</label>
                        <select name="client_type" id="client_type" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                            @foreach($clientTypes as $type)
                                <option value="{{ $type->value }}" {{ old('client_type') == $type->value ? 'selected' : '' }}>
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('client_type')" class="mt-1" />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Correo Electrónico</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">Teléfono</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                    </div>

                    <div class="col-span-2">
                        <label for="address" class="block text-sm font-semibold text-gray-700 mb-1">Dirección (Opcional)</label>
                        <textarea name="address" id="address" rows="3" class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">{{ old('address') }}</textarea>
                        <x-input-error :messages="$errors->get('address')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-4">
                <a href="{{ route('clients.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">Cancelar</a>
                <button type="submit" class="bg-accent text-white px-6 py-2 rounded-xl font-bold hover:bg-accent/90 transition-colors shadow-sm shadow-accent/20">
                    Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</x-app-layout>