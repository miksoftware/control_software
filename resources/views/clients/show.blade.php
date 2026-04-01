<x-app-layout>
    <div class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between">
        <div>
            <a href="{{ route('clients.index') }}" class="text-accent hover:text-accent/80 flex items-center text-sm font-medium mb-2 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al listado
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ $client->name }}</h1>
            <div class="flex items-center mt-2 space-x-4">
                @if($client->client_type->value === 'reseller')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 uppercase tracking-wider">
                        Revendedor
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">
                        Cliente Final
                    </span>
                @endif
                <span class="text-gray-400 text-sm">Cliente desde {{ $client->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="{{ route('clients.statement', $client) }}" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-xl font-bold hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition ease-in-out duration-150 shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 2v-6m-8 13h11a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Estado de Cuenta
            </a>
            <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150 shadow-sm">
                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Editar Perfil
            </a>
            <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este cliente? Esta acción no se puede deshacer.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-50 border border-red-100 rounded-xl font-bold text-red-600 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Eliminar
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Información de Contacto</h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center mr-3 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Email</p>
                            <p class="text-sm text-gray-800 font-medium">{{ $client->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center mr-3 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Teléfono</p>
                            <p class="text-sm text-gray-800 font-medium">{{ $client->phone ?? 'No registrado' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center mr-3 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Dirección</p>
                            <p class="text-sm text-gray-800 font-medium leading-relaxed">{{ $client->address ?? 'No registrada' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-primary p-6 rounded-2xl shadow-sm text-white">
                <h3 class="text-lg font-bold mb-4">Resumen de Cuenta</h3>
                <div class="space-y-4">
                    <div class="bg-white/10 p-4 rounded-xl">
                        <p class="text-white/60 text-[10px] uppercase font-bold mb-1">Saldo Pendiente</p>
                        <p class="text-2xl font-bold text-accent">${{ number_format($client->global_pending_balance, 2) }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white/10 p-4 rounded-xl">
                            <p class="text-white/60 text-[10px] uppercase font-bold mb-1">Licencias</p>
                            <p class="text-2xl font-bold">{{ $client->mikposLicenses->count() }}</p>
                        </div>
                        <div class="bg-white/10 p-4 rounded-xl">
                            <p class="text-white/60 text-[10px] uppercase font-bold mb-1">Proyectos</p>
                            <p class="text-2xl font-bold">{{ $client->customProjects->count() + $client->mikposFeatures->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Tabs Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Licencias Section -->
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Licencias MikPoS</h2>
                    <a href="{{ route('licenses.create', ['client_id' => $client->id]) }}" class="text-accent text-sm font-bold hover:underline">+ Nueva Licencia</a>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Licencia / Ciclo</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Próx. Facturación</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($client->mikposLicenses as $license)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-800">{{ $license->license_key }}</div>
                                        <div class="text-xs text-gray-500 uppercase">{{ $license->billing_cycle->label() }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $license->next_billing_at ? $license->next_billing_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $license->status === App\Enums\LicenseStatus::Active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800' }} uppercase">
                                            {{ $license->status->label() }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">No hay licencias registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Proyectos y Mejoras Section -->
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Proyectos y Mejoras</h2>
                <div class="space-y-4">
                    @foreach($client->customProjects as $project)
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-xl bg-accent/10 text-accent flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 2.944a11.955 11.955 0 01-8.618 3.04m0 0a11.955 11.955 0 00-3.382 8.58c0 5.89 4.778 10.667 10.667 10.667m12-11.58a11.955 11.955 0 01-3.382 8.58m3.382-8.58a11.955 11.955 0 00-3.382-8.58m0 0A11.955 11.955 0 0012 2.944m0 0a11.955 11.955 0 018.618 3.04"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ $project->name }}</h4>
                                    <p class="text-xs text-gray-500">Proyecto a Medida • {{ $project->status->label() }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">${{ number_format($project->contract_value, 2) }}</p>
                                <a href="{{ route('projects.show', $project) }}" class="text-[10px] text-accent font-bold uppercase hover:underline">Ver detalles</a>
                            </div>
                        </div>
                    @endforeach

                    @foreach($client->mikposFeatures as $feature)
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ $feature->title }}</h4>
                                    <p class="text-xs text-gray-500">Mejora MikPoS • {{ $feature->status->label() }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">${{ number_format($feature->total_cost, 2) }}</p>
                                <a href="{{ route('features.show', $feature) }}" class="text-[10px] text-accent font-bold uppercase hover:underline">Ver detalles</a>
                            </div>
                        </div>
                    @endforeach

                    @if($client->customProjects->isEmpty() && $client->mikposFeatures->isEmpty())
                        <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center text-gray-400">
                            No hay proyectos o mejoras registradas.
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>