<x-app-layout>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-500 mt-1">Bienvenido al sistema de gestión de MIKSOFTWARE.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Stats Cards -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-primary/10 text-primary rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <span class="text-emerald-500 text-xs font-bold">+12%</span>
            </div>
            <p class="text-sm font-medium text-gray-500">Clientes Totales</p>
            <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Client::count() }}</p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-accent/10 text-accent rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <span class="text-emerald-500 text-xs font-bold">+5%</span>
            </div>
            <p class="text-sm font-medium text-gray-500">Licencias Activas</p>
            <p class="text-2xl font-bold text-gray-900">{{ \App\Models\MikposLicense::where('status', 'active')->count() }}</p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-sm font-medium text-gray-500">Ingresos del Mes</p>
            <p class="text-2xl font-bold text-gray-900">${{ number_format(\App\Models\Payment::whereMonth('paid_at', now()->month)->sum('amount'), 2) }}</p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-red-100 text-red-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-sm font-medium text-gray-500">Saldo Pendiente</p>
            @php
                $totalContract = \App\Models\CustomProject::sum('contract_value') + \App\Models\MikposFeature::sum('total_cost');
                $totalPaid = \App\Models\Payment::sum('amount');
                $pending = $totalContract - $totalPaid;
            @endphp
            <p class="text-2xl font-bold text-red-500">${{ number_format($pending, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Clients -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">Clientes Recientes</h3>
                <a href="{{ route('clients.index') }}" class="text-xs font-bold text-accent uppercase hover:underline">Ver todos</a>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach(\App\Models\Client::orderByDesc('created_at')->limit(5)->get() as $client)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold mr-3 text-gray-500">
                                {{ substr($client->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $client->name }}</p>
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest">{{ $client->client_type->value }}</p>
                            </div>
                        </div>
                        <a href="{{ route('clients.show', $client) }}" class="text-gray-400 hover:text-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">Últimos Pagos</h3>
                <a href="{{ route('payments.index') }}" class="text-xs font-bold text-accent uppercase hover:underline">Ver todos</a>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach(\App\Models\Payment::with('client')->orderByDesc('paid_at')->limit(5)->get() as $payment)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg mr-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">${{ number_format($payment->amount, 2) }}</p>
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest">{{ $payment->client->name }} • {{ $payment->paid_at->format('d M') }}</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-600 uppercase bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">Completado</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>