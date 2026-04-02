<x-app-layout>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Clientes</h1>
            <p class="text-gray-500 mt-1">Gestiona la base de datos de tus clientes y revendedores.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('clients.create') }}" class="inline-flex items-center px-4 py-2 bg-accent border border-transparent rounded-xl font-bold text-white hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Nuevo Cliente
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
        <form action="{{ route('clients.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nombre, email o teléfono..." class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
            </div>
            <div>
                <label for="client_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Cliente</label>
                <select name="client_type" id="client_type" class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                    <option value="">Todos los tipos</option>
                    <option value="final" {{ request('client_type') == 'final' ? 'selected' : '' }}>Cliente Final</option>
                    <option value="reseller" {{ request('client_type') == 'reseller' ? 'selected' : '' }}>Revendedor</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-primary text-white px-4 py-2 rounded-xl font-medium hover:bg-primary/90 transition-colors">
                    Filtrar Resultados
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
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Contacto</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Resumen</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($clients as $client)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold mr-3">
                                        {{ substr($client->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ $client->name }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ str_pad((string)$client->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($client->client_type->value === 'reseller')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Revendedor
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Cliente Final
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div class="flex flex-col">
                                    <span>{{ $client->email }}</span>
                                    <span class="text-xs text-gray-400">{{ $client->phone ?? 'Sin teléfono' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center space-x-4">
                                    <div class="text-center" title="Licencias">
                                        <div class="text-xs font-bold text-gray-900">{{ $client->mikpos_licenses_count }}</div>
                                        <div class="text-[10px] uppercase text-gray-400">Lic</div>
                                    </div>
                                    <div class="text-center" title="Proyectos/Mejoras">
                                        <div class="text-xs font-bold text-gray-900">{{ $client->custom_projects_count + $client->mikpos_features_count }}</div>
                                        <div class="text-[10px] uppercase text-gray-400">Proy</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('clients.show', $client) }}" class="text-gray-400 hover:text-primary transition-colors">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('clients.edit', $client) }}" class="text-gray-400 hover:text-accent transition-colors"
                                   onclick="event.preventDefault(); openEditClient({{ json_encode(['id' => $client->id, 'name' => $client->name, 'client_type' => $client->client_type->value, 'email' => $client->email, 'phone' => $client->phone, 'address' => $client->address]) }})">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No se encontraron clientes con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($clients->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $clients->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Editar Cliente -->
    <div id="editClientModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="document.getElementById('editClientModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-auto">
                <form id="editClientForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="px-8 py-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900">Editar Cliente</h3>
                            <button type="button" onclick="document.getElementById('editClientModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="px-8 py-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo / Empresa *</label>
                            <input type="text" name="name" id="editClientName" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Cliente</label>
                                <select name="client_type" id="editClientType" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                    @foreach($clientTypes as $type)
                                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico *</label>
                                <input type="email" name="email" id="editClientEmail" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text" name="phone" id="editClientPhone" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            <textarea name="address" id="editClientAddress" rows="2" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm"></textarea>
                        </div>
                    </div>
                    <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-3 rounded-b-2xl">
                        <button type="button" onclick="document.getElementById('editClientModal').classList.add('hidden')" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancelar</button>
                        <button type="submit" class="px-6 py-2 bg-accent text-white text-sm font-bold rounded-xl hover:bg-accent/90 transition-colors shadow-sm shadow-accent/20">Actualizar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openEditClient(c) {
        document.getElementById('editClientForm').action = '/clients/' + c.id;
        document.getElementById('editClientName').value = c.name || '';
        document.getElementById('editClientType').value = c.client_type || '';
        document.getElementById('editClientEmail').value = c.email || '';
        document.getElementById('editClientPhone').value = c.phone || '';
        document.getElementById('editClientAddress').value = c.address || '';
        document.getElementById('editClientModal').classList.remove('hidden');
    }
    </script>
</x-app-layout>