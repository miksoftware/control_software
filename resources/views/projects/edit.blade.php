<x-app-layout>
    @section('title', 'Editar Proyecto')

    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <a href="{{ route('projects.show', $project) }}" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
            <h2 class="font-bold text-xl text-gray-800">Editar: {{ $project->name }}</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-5">
                @csrf @method('PUT')

                <div><label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" name="name" value="{{ old('name', $project->name) }}" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div><label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea name="description" rows="4" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $project->description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Valor del Contrato</label>
                        <input type="number" name="contract_value" value="{{ old('contract_value', $project->contract_value) }}" step="0.01" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" {{ $project->status->value === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Fin Estimado</label>
                        <input type="date" name="estimated_end_date" value="{{ old('estimated_end_date', $project->estimated_end_date?->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Fin Real</label>
                        <input type="date" name="actual_end_date" value="{{ old('actual_end_date', $project->actual_end_date?->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('projects.show', $project) }}" class="px-4 py-2 text-gray-600 hover:text-gray-800 text-sm font-medium">Cancelar</a>
                    <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
