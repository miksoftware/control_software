<x-app-layout>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Proyectos a Medida</h1>
            <p class="text-gray-500 mt-1">Gestión de desarrollos personalizados y contratos especiales.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <button onclick="document.getElementById('createProjectModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-accent border border-transparent rounded-xl font-bold text-white hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150 shadow-sm shadow-accent/20">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Nuevo Proyecto
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Proyecto / Cliente</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Valor Total</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($projects as $project)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $project->name }}</div>
                                <div class="text-xs text-gray-500">{{ $project->client->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">
                                    {{ $project->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                ${{ number_format($project->contract_value, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('projects.show', $project) }}" class="text-gray-400 hover:text-primary transition-colors">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('projects.edit', $project) }}" class="text-gray-400 hover:text-accent transition-colors"
                                   onclick="event.preventDefault(); openEditProject({{ json_encode(['id' => $project->id, 'name' => $project->name, 'description' => $project->description, 'contract_value' => $project->contract_value, 'status' => $project->status->value, 'start_date' => $project->start_date?->format('Y-m-d'), 'estimated_end_date' => $project->estimated_end_date?->format('Y-m-d'), 'actual_end_date' => $project->actual_end_date?->format('Y-m-d')]) }})">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500 italic">
                                No se encontraron proyectos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($projects->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $projects->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Nuevo Proyecto -->
    <div id="createProjectModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="document.getElementById('createProjectModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-2xl sm:w-full mx-auto">
                <form method="POST" action="{{ route('projects.store') }}">
                    @csrf
                    <div class="px-8 py-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900">Nuevo Proyecto a Medida</h3>
                            <button type="button" onclick="document.getElementById('createProjectModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="px-8 py-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                            <select name="client_id" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                <option value="">Seleccionar...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Proyecto *</label>
                            <input type="text" name="name" required placeholder="Ej: CRM Personalizado para Empresa X" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea name="description" rows="3" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Valor del Contrato *</label>
                                <input type="number" name="contract_value" step="0.01" min="0" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                                <input type="date" name="start_date" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin Estimada</label>
                                <input type="date" name="estimated_end_date" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-3 rounded-b-2xl">
                        <button type="button" onclick="document.getElementById('createProjectModal').classList.add('hidden')" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancelar</button>
                        <button type="submit" class="px-6 py-2 bg-accent text-white text-sm font-bold rounded-xl hover:bg-accent/90 transition-colors shadow-sm shadow-accent/20">Crear Proyecto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($errors->any())
    <script>document.addEventListener('DOMContentLoaded', () => document.getElementById('createProjectModal').classList.remove('hidden'));</script>
    @endif

    <!-- Modal: Editar Proyecto -->
    <div id="editProjectModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="document.getElementById('editProjectModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-auto">
                <form id="editProjectForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="px-8 py-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900">Editar Proyecto</h3>
                            <button type="button" onclick="document.getElementById('editProjectModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="px-8 py-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                            <input type="text" name="name" id="editProjectName" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea name="description" id="editProjectDescription" rows="3" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Valor del Contrato</label>
                                <input type="number" name="contract_value" id="editProjectValue" step="0.01" min="0" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="status" id="editProjectStatus" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                                <input type="date" name="start_date" id="editProjectStart" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fin Estimado</label>
                                <input type="date" name="estimated_end_date" id="editProjectEstEnd" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fin Real</label>
                                <input type="date" name="actual_end_date" id="editProjectActEnd" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-3 rounded-b-2xl">
                        <button type="button" onclick="document.getElementById('editProjectModal').classList.add('hidden')" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancelar</button>
                        <button type="submit" class="px-6 py-2 bg-accent text-white text-sm font-bold rounded-xl hover:bg-accent/90 transition-colors shadow-sm shadow-accent/20">Actualizar Proyecto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openEditProject(p) {
        document.getElementById('editProjectForm').action = '/projects/' + p.id;
        document.getElementById('editProjectName').value = p.name || '';
        document.getElementById('editProjectDescription').value = p.description || '';
        document.getElementById('editProjectValue').value = p.contract_value || '';
        document.getElementById('editProjectStatus').value = p.status || '';
        document.getElementById('editProjectStart').value = p.start_date || '';
        document.getElementById('editProjectEstEnd').value = p.estimated_end_date || '';
        document.getElementById('editProjectActEnd').value = p.actual_end_date || '';
        document.getElementById('editProjectModal').classList.remove('hidden');
    }
    </script>
</x-app-layout>