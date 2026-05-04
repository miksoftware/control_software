@section('title', 'Reporte Detallado')

<x-app-layout>
    {{-- Print-only header --}}
    <div class="hidden print:block mb-6">
        <div class="flex items-center justify-between border-b-2 border-gray-300 pb-4">
            <div class="text-2xl font-bold tracking-tight">
                MIK<span style="color: #FF7152;">SOFTWARE</span>
            </div>
            <div class="text-sm text-gray-500">Generado: {{ $generated_at }}</div>
        </div>
    </div>

    {{-- Header --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between print:hidden">
        <div>
            <a href="{{ route('clients.show', $client) }}" class="text-accent hover:text-accent/80 flex items-center text-sm font-medium mb-2 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al perfil
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Reporte Detallado</h1>
            <p class="text-gray-500 mt-1">Información completa para <strong>{{ $client->name }}</strong>.</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="{{ route('clients.report.export', $client) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition ease-in-out duration-150 shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Descargar Excel
            </a>
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-xl font-bold hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition ease-in-out duration-150 shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir Reporte
            </button>
        </div>
    </div>

    {{-- Client Info Card --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
            <div class="flex items-center space-x-3">
                <h2 class="text-xl font-bold text-gray-900">{{ $client->name }}</h2>
                @if($client->client_type === \App\Enums\ClientType::Reseller)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 uppercase tracking-wider">Revendedor</span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">Cliente Final</span>
                @endif
            </div>
            <div class="mt-2 md:mt-0 text-sm text-gray-400">
                Generado: {{ $generated_at }}
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Email</span>
                <p class="text-gray-800 font-medium">{{ $client->email }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Teléfono</span>
                <p class="text-gray-800 font-medium">{{ $client->phone ?? 'No registrado' }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Empresa</span>
                <p class="text-gray-800 font-medium">{{ $client->company_name ?? 'No registrada' }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Dirección</span>
                <p class="text-gray-800 font-medium">{{ $client->address ?? 'No registrada' }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Tipo de Cliente</span>
                <p class="text-gray-800 font-medium">{{ $client->client_type->label() }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Cliente desde</span>
                <p class="text-gray-800 font-medium">{{ $client->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Financial Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- Total Deuda Acumulada --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Deuda Acumulada</p>
            <p class="text-3xl font-bold text-gray-900">${{ number_format($financial_summary['total_debt'], 2) }}</p>
            <div class="mt-2 text-[10px] text-gray-400">
                Proyectos (${{ number_format($financial_summary['total_projects_debt'], 2) }}) + Mejoras (${{ number_format($financial_summary['total_features_debt'], 2) }})
            </div>
        </div>

        {{-- Total Abonos Realizados --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-emerald-500">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Abonos Realizados</p>
            <p class="text-3xl font-bold text-emerald-600">${{ number_format($financial_summary['total_paid'], 2) }}</p>
            <div class="mt-2 text-[10px] text-emerald-500 uppercase font-bold tracking-tighter">{{ $financial_summary['payments_count'] }} pagos registrados</div>
        </div>

        {{-- Saldo Pendiente --}}
        <div class="p-6 rounded-2xl shadow-lg text-white relative overflow-hidden" style="background-color: #1B0B3B;">
            <div class="relative z-10">
                <p class="text-xs font-bold text-white/60 uppercase tracking-wider mb-2">Saldo Pendiente a la Fecha</p>
                <p class="text-4xl font-extrabold" style="color: #FF7152;">${{ number_format($financial_summary['outstanding_balance'], 2) }}</p>
                <p class="mt-2 text-[10px] text-white/40 italic">Corte realizado el {{ $generated_at }}</p>
            </div>
            <svg class="absolute right-[-20px] bottom-[-20px] w-32 h-32 text-white/5 opacity-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>

    {{-- Payment Progress Bar --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-bold text-gray-700">Progreso de Pago</p>
            <p class="text-sm font-bold" style="color: #FF7152;">{{ number_format($financial_summary['payment_progress'], 1) }}%</p>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
            <div class="h-3 rounded-full transition-all duration-500" style="width: {{ min($financial_summary['payment_progress'], 100) }}%; background-color: #FF7152;"></div>
        </div>
        <div class="flex items-center justify-between mt-2 text-[10px] text-gray-400">
            <span>Pagado: ${{ number_format($financial_summary['total_paid'], 2) }}</span>
            <span>Deuda Total: ${{ number_format($financial_summary['total_debt'], 2) }}</span>
        </div>
    </div>

    {{-- Reseller Information (conditional) --}}
    @if($reseller_summary)
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
        <div class="flex items-center space-x-3 mb-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 uppercase tracking-wider">Revendedor</span>
            <h2 class="text-xl font-bold text-gray-900">Información de Revendedor</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-purple-50 rounded-xl p-4">
                <p class="text-xs font-bold text-purple-400 uppercase tracking-wider mb-1">Total Licencias</p>
                <p class="text-2xl font-bold text-purple-800">{{ $reseller_summary['total_licenses'] }}</p>
            </div>
            <div class="bg-purple-50 rounded-xl p-4">
                <p class="text-xs font-bold text-purple-400 uppercase tracking-wider mb-1">Licencias Pagadas</p>
                <p class="text-2xl font-bold text-purple-800">{{ $reseller_summary['paid_licenses'] }}</p>
            </div>
            <div class="bg-purple-50 rounded-xl p-4">
                <p class="text-xs font-bold text-purple-400 uppercase tracking-wider mb-1">Licencias Gratuitas</p>
                <p class="text-2xl font-bold text-purple-800">{{ $reseller_summary['free_licenses'] }}</p>
            </div>
            <div class="bg-purple-50 rounded-xl p-4 sm:col-span-2 lg:col-span-3">
                <p class="text-xs font-bold text-purple-400 uppercase tracking-wider mb-1">Próxima Licencia Gratuita</p>
                @if($reseller_summary['next_license_is_free'])
                    <p class="text-lg font-bold text-emerald-600">¡La próxima licencia es gratuita!</p>
                @else
                    <p class="text-lg font-bold text-purple-800">Faltan <span style="color: #FF7152;">{{ $reseller_summary['remaining_for_next_free'] }}</span> licencias pagadas para la próxima gratuita</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Licencias MikPoS (collapsible) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8" x-data="{ open: false }">
        <button @click="open = !open" class="w-full flex items-center justify-between p-6 text-left focus:outline-none">
            <div class="flex items-center space-x-3">
                <h2 class="text-xl font-bold text-gray-900">Licencias MikPoS</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">{{ $licenses->count() }}</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="open" x-collapse>
            <div class="px-6 pb-6">
                @if($licenses->count() > 0)
                    <div class="space-y-4">
                        @foreach($licenses as $license)
                            @php
                                $statusColors = match($license->status) {
                                    \App\Enums\LicenseStatus::Active => 'bg-emerald-50 text-emerald-600',
                                    \App\Enums\LicenseStatus::Suspended => 'bg-yellow-50 text-yellow-600',
                                    \App\Enums\LicenseStatus::Cancelled => 'bg-red-50 text-red-600',
                                };
                            @endphp
                            <div class="border border-gray-100 rounded-xl p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3">
                                    <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                        <span class="font-mono text-sm font-bold text-gray-900">{{ $license->license_key }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $statusColors }}">{{ $license->status->label() }}</span>
                                        @if($license->is_free_promotion)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold text-accent bg-accent/10">Promoción Gratuita</span>
                                        @endif
                                    </div>
                                    @if($license->activated_at)
                                        <span class="text-xs text-gray-400 mt-1 sm:mt-0">Activada: {{ $license->activated_at->format('d/m/Y') }}</span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                                    <div>
                                        <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Sitio Web</span>
                                        <p class="text-gray-800 font-medium">{{ $license->site_url ?? 'No registrado' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Ciclo de Facturación</span>
                                        <p class="text-gray-800 font-medium">{{ $license->billing_cycle->label() }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Tarifa Mensual</span>
                                        <p class="text-gray-800 font-medium">${{ number_format($license->monthly_rate, 2) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Cuota de Instalación</span>
                                        <p class="text-gray-800 font-medium">${{ number_format($license->installation_fee, 2) }}</p>
                                    </div>
                                </div>

                                {{-- Nested features --}}
                                @if($license->features->count() > 0)
                                    <div class="mt-4 border-t border-gray-100 pt-3">
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Mejoras / Cambios ({{ $license->features->count() }})</p>
                                        <div class="space-y-2">
                                            @foreach($license->features as $feature)
                                                @php
                                                    $featureStatusColors = match($feature->status) {
                                                        \App\Enums\ProjectStatus::Completed => 'bg-emerald-50 text-emerald-600',
                                                        \App\Enums\ProjectStatus::Pending => 'bg-yellow-50 text-yellow-600',
                                                        \App\Enums\ProjectStatus::InProgress => 'bg-blue-50 text-blue-600',
                                                        \App\Enums\ProjectStatus::Cancelled => 'bg-red-50 text-red-600',
                                                    };
                                                @endphp
                                                <div class="bg-gray-50 rounded-lg p-3">
                                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                                        <div class="flex items-center space-x-2">
                                                            <span class="text-sm font-bold text-gray-800">{{ $feature->title }}</span>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $featureStatusColors }}">{{ $feature->status->label() }}</span>
                                                        </div>
                                                        <span class="text-sm font-bold text-gray-700 mt-1 sm:mt-0">${{ number_format($feature->total_cost, 2) }}</span>
                                                    </div>
                                                    @if($feature->description)
                                                        <p class="text-xs text-gray-500 mt-1">{{ $feature->description }}</p>
                                                    @endif
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        @if($feature->completed_at)
                                                            Completado: {{ $feature->completed_at->format('d/m/Y') }}
                                                        @elseif($feature->estimated_delivery_at)
                                                            Entrega estimada: {{ $feature->estimated_delivery_at->format('d/m/Y') }}
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Active monthly subtotal --}}
                    <div class="mt-4 pt-4 border-t border-gray-200 flex flex-wrap items-center justify-between gap-2">
                        <span class="text-sm font-bold text-gray-700">Subtotal Mensual Recurrente (licencias activas)</span>
                        <span class="text-lg font-bold" style="color: #FF7152;">${{ number_format($active_monthly_total, 2) }}</span>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-400 text-sm">No se han registrado licencias para este cliente.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Proyectos a Medida (collapsible) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8" x-data="{ open: false }">
        <button @click="open = !open" class="w-full flex items-center justify-between p-6 text-left focus:outline-none">
            <div class="flex items-center space-x-3">
                <h2 class="text-xl font-bold text-gray-900">Proyectos a Medida</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">{{ $projects->count() }}</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="open" x-collapse>
            <div class="px-6 pb-6">
                @if($projects->count() > 0)
                    <div class="space-y-4">
                        @foreach($projects as $project)
                            @php
                                $projectStatusColors = match($project->status) {
                                    \App\Enums\ProjectStatus::Completed => 'bg-emerald-50 text-emerald-600',
                                    \App\Enums\ProjectStatus::Pending => 'bg-yellow-50 text-yellow-600',
                                    \App\Enums\ProjectStatus::InProgress => 'bg-blue-50 text-blue-600',
                                    \App\Enums\ProjectStatus::Cancelled => 'bg-red-50 text-red-600',
                                };
                            @endphp
                            <div class="border border-gray-100 rounded-xl p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3">
                                    <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                        <span class="text-sm font-bold text-gray-900">{{ $project->name }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $projectStatusColors }}">{{ $project->status->label() }}</span>
                                        @if($project->is_overdue)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-600">Retrasado</span>
                                        @endif
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 mt-1 sm:mt-0">${{ number_format($project->contract_value, 2) }}</span>
                                </div>

                                @if($project->description)
                                    <p class="text-xs text-gray-500 mb-3">{{ $project->description }}</p>
                                @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Fecha de Inicio</span>
                                        <p class="text-gray-800 font-medium">{{ $project->start_date ? $project->start_date->format('d/m/Y') : 'No definida' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Fecha Estimada de Fin</span>
                                        <p class="text-gray-800 font-medium">{{ $project->estimated_end_date ? $project->estimated_end_date->format('d/m/Y') : 'No definida' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Fecha Real de Fin</span>
                                        <p class="text-gray-800 font-medium">{{ $project->actual_end_date ? $project->actual_end_date->format('d/m/Y') : 'No definida' }}</p>
                                    </div>
                                </div>

                                {{-- Project payments --}}
                                @if($project->payments->count() > 0)
                                    <div class="mt-4 border-t border-gray-100 pt-3">
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pagos Asociados ({{ $project->payments->count() }})</p>
                                        <div class="space-y-2">
                                            @foreach($project->payments as $payment)
                                                <div class="bg-gray-50 rounded-lg p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <span class="text-xs text-gray-400">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y') : 'Sin fecha' }}</span>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">{{ $payment->payment_method->label() }}</span>
                                                    </div>
                                                    <span class="text-sm font-bold text-emerald-600 mt-1 sm:mt-0">${{ number_format($payment->amount, 2) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Subtotal debt for projects --}}
                    <div class="mt-4 pt-4 border-t border-gray-200 flex flex-wrap items-center justify-between gap-2">
                        <span class="text-sm font-bold text-gray-700">Subtotal Deuda Proyectos</span>
                        <span class="text-lg font-bold" style="color: #FF7152;">${{ number_format($financial_summary['total_projects_debt'], 2) }}</span>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-400 text-sm">No se han registrado proyectos para este cliente.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Mejoras Generales MikPoS (collapsible, conditional) --}}
    @if($orphan_features->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8" x-data="{ open: false }">
        <button @click="open = !open" class="w-full flex items-center justify-between p-6 text-left focus:outline-none">
            <div class="flex items-center space-x-3">
                <h2 class="text-xl font-bold text-gray-900">Mejoras Generales MikPoS</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">{{ $orphan_features->count() }}</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="open" x-collapse>
            <div class="px-6 pb-6">
                <div class="space-y-4">
                    @foreach($orphan_features as $feature)
                        @php
                            $featureStatusColors = match($feature->status) {
                                \App\Enums\ProjectStatus::Completed => 'bg-emerald-50 text-emerald-600',
                                \App\Enums\ProjectStatus::Pending => 'bg-yellow-50 text-yellow-600',
                                \App\Enums\ProjectStatus::InProgress => 'bg-blue-50 text-blue-600',
                                \App\Enums\ProjectStatus::Cancelled => 'bg-red-50 text-red-600',
                            };
                        @endphp
                        <div class="border border-gray-100 rounded-xl p-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-2">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-bold text-gray-900">{{ $feature->title }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $featureStatusColors }}">{{ $feature->status->label() }}</span>
                                </div>
                                <span class="text-sm font-bold text-gray-700 mt-1 sm:mt-0">${{ number_format($feature->total_cost, 2) }}</span>
                            </div>
                            @if($feature->description)
                                <p class="text-xs text-gray-500 mb-2">{{ $feature->description }}</p>
                            @endif
                            <div class="text-xs text-gray-400">
                                @if($feature->completed_at)
                                    Completado: {{ $feature->completed_at->format('d/m/Y') }}
                                @elseif($feature->estimated_delivery_at)
                                    Entrega estimada: {{ $feature->estimated_delivery_at->format('d/m/Y') }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Historial de Pagos (colapsable) --}}
    @php
        $allPayments = $client->payments->sortByDesc('paid_at');
        $categoryLabels = ['global' => 'Global', 'projects' => 'Proyectos', 'features' => 'Mejoras'];
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8" x-data="{ open: false }">
        <button @click="open = !open" class="w-full flex items-center justify-between p-6 text-left focus:outline-none">
            <div class="flex items-center space-x-3">
                <h2 class="text-xl font-bold text-gray-900">Historial de Pagos</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">{{ $allPayments->count() }}</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="open" x-collapse>
            <div class="px-6 pb-6">
                @if($allPayments->count() > 0)
                    <div class="overflow-x-auto">
                    <div class="space-y-3 min-w-0">
                        @foreach($allPayments as $payment)
                            <div class="border border-gray-100 rounded-xl p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-2">
                                    <div class="flex items-center space-x-3 flex-wrap gap-y-1">
                                        <span class="text-xs text-gray-400">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y') : 'Sin fecha' }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">{{ $payment->payment_method->label() }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-600">{{ $categoryLabels[$payment->category] ?? $payment->category }}</span>
                                    </div>
                                    <span class="text-sm font-bold text-emerald-600 mt-1 sm:mt-0">${{ number_format($payment->amount, 2) }}</span>
                                </div>

                                @if($payment->category === 'projects' && $payment->custom_project_id && $payment->customProject)
                                    <p class="text-xs text-gray-500 mb-1">
                                        <span class="font-bold text-gray-600">Proyecto:</span> {{ $payment->customProject->name }}
                                    </p>
                                @endif

                                @if($payment->reference)
                                    <p class="text-xs text-gray-500">
                                        <span class="font-bold text-gray-600">Referencia:</span> {{ $payment->reference }}
                                    </p>
                                @endif

                                @if($payment->notes)
                                    <p class="text-xs text-gray-400 mt-1">{{ $payment->notes }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    </div>{{-- close overflow-x-auto wrapper --}}

                    {{-- Subtotales por categoría --}}
                    <div class="mt-6 pt-4 border-t border-gray-200 space-y-2">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Subtotales por Categoría</p>
                        @foreach(['global', 'projects', 'features'] as $cat)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">{{ $categoryLabels[$cat] }}</span>
                                <span class="font-bold text-gray-700">${{ number_format($payments_by_category[$cat]['subtotal'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Gran total --}}
                    <div class="mt-4 pt-4 border-t border-gray-200 flex flex-wrap items-center justify-between gap-2">
                        <span class="text-sm font-bold text-gray-700">Total General</span>
                        <span class="text-lg font-bold" style="color: #FF7152;">${{ number_format($payments_by_category['grand_total'], 2) }}</span>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-400 text-sm">No se han registrado pagos para este cliente.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Print Styles --}}
    <style>
        @media print {
            /* Hide sidebar, mobile header, and overlay */
            aside,
            header.lg\:hidden,
            .lg\:hidden {
                display: none !important;
            }

            /* Hide elements marked with print:hidden (action buttons, back link) */
            .print\:hidden {
                display: none !important;
            }

            /* Show print-only header */
            .hidden.print\:block {
                display: block !important;
            }

            /* Override Alpine.js x-show (which sets display:none inline) */
            [x-show] {
                display: block !important;
            }

            /* Main content takes full width without sidebar offset */
            .flex.h-screen {
                display: block !important;
                height: auto !important;
                overflow: visible !important;
            }

            .flex-1.flex.flex-col {
                display: block !important;
                overflow: visible !important;
            }

            main {
                padding: 0 !important;
                overflow: visible !important;
            }

            /* Prevent page breaks inside cards and tables */
            .rounded-2xl,
            .rounded-xl,
            table {
                break-inside: avoid;
            }

            /* Remove shadows and reduce visual noise */
            .shadow-sm,
            .shadow-lg {
                box-shadow: none !important;
            }

            /* Ensure all text is black for print readability */
            body,
            p,
            span,
            h1, h2, h3, h4, h5, h6,
            div,
            td, th {
                color: #000 !important;
            }

            /* Preserve accent colors for key financial figures */
            .text-emerald-600 {
                color: #059669 !important;
            }

            [style*="color: #FF7152"] {
                color: #FF7152 !important;
            }

            /* Reduce padding for compact print */
            .p-6 {
                padding: 0.75rem !important;
            }

            .px-6 {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            .mb-8 {
                margin-bottom: 1rem !important;
            }

            /* Ensure borders are visible for structure */
            .border,
            .border-gray-100 {
                border-color: #d1d5db !important;
            }
        }
    </style>

</x-app-layout>
