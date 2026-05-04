<x-app-layout>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Licencias MikPoS</h1>
            <p class="text-gray-500 mt-1">Control de activaciones, ciclos de facturación y promociones.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <button onclick="document.getElementById('createLicenseModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-accent border border-transparent rounded-xl font-bold text-white hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition ease-in-out duration-150 shadow-sm shadow-accent/20">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Nueva Licencia
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
        <form action="{{ route('licenses.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="status" id="status" class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                    <option value="">Todos los estados</option>
                    @foreach(\App\Enums\LicenseStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="is_free_promotion" class="block text-sm font-medium text-gray-700 mb-1">Promoción Gratuita</label>
                <select name="is_free_promotion" id="is_free_promotion" class="block w-full border-gray-300 rounded-xl bg-gray-50 focus:ring-accent focus:border-accent sm:text-sm">
                    <option value="">Todas</option>
                    <option value="1" {{ request('is_free_promotion') === '1' ? 'selected' : '' }}>Solo Gratuitas (5ta)</option>
                    <option value="0" {{ request('is_free_promotion') === '0' ? 'selected' : '' }}>Solo Pagas</option>
                </select>
            </div>
            <div class="md:col-span-1 flex items-end">
                <button type="submit" class="w-full bg-primary text-white px-4 py-2 rounded-xl font-medium hover:bg-primary/90 transition-colors">
                    Filtrar
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
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">URL Sitio</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan / Ciclo</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Próximo Pago</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Promoción</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($licenses as $license)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-lg bg-accent/10 text-accent flex items-center justify-center font-bold mr-3 text-xs">
                                        {{ substr($license->client->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ $license->client->name }}</div>
                                        <div class="text-[10px] text-gray-400 uppercase tracking-widest">{{ $license->license_key ?? 'KEY-PENDIENTE' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($license->site_url)
                                    <a href="{{ $license->site_url }}" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-800 break-all">{{ Str::limit($license->site_url, 30) }}</a>
                                @else
                                    <span class="text-xs text-gray-300 italic">Sin URL</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-800 font-medium">{{ $license->billing_cycle->label() }}</div>
                                <div class="text-xs text-gray-500">${{ number_format($license->monthly_rate, 2) }} / mes</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($license->status === \App\Enums\LicenseStatus::Active)
                                    <div class="text-sm text-gray-800 font-medium">
                                        {{ $license->next_billing_at ? $license->next_billing_at->format('d/m/Y') : 'N/A' }}
                                    </div>
                                    <div class="text-[10px] text-emerald-600 font-bold uppercase">{{ $license->status->label() }}</div>
                                @else
                                    <div class="text-sm text-gray-400 font-medium">--</div>
                                    <div class="text-[10px] text-red-400 font-bold uppercase">{{ $license->status->label() }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($license->is_free_promotion)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        Gratis (5ta)
                                    </span>
                                @else
                                    <span class="text-gray-300 text-[10px] font-medium uppercase italic">Estándar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button type="button" onclick="openShowLicense({{ json_encode(['id' => $license->id, 'license_key' => $license->license_key, 'client_name' => $license->client->name, 'client_type' => $license->client->client_type->label(), 'site_url' => $license->site_url, 'billing_cycle' => $license->billing_cycle->label(), 'monthly_rate' => $license->monthly_rate, 'installation_fee' => $license->installation_fee, 'cycle_amount' => $license->cycle_amount, 'activated_at' => $license->activated_at?->format('d/m/Y'), 'next_billing_at' => $license->next_billing_at?->format('d/m/Y'), 'status' => $license->status->label(), 'status_color' => $license->status->value === 'active' ? 'emerald' : ($license->status->value === 'suspended' ? 'amber' : 'red'), 'is_free_promotion' => $license->is_free_promotion, 'is_reseller' => $license->client->is_reseller, 'reseller_summary' => $resellerSummaries[$license->client_id] ?? null, 'features_count' => $license->features_count ?? 0, 'system_enabled' => $license->system_enabled, 'has_system_token' => !empty($license->system_token)]) }})" class="text-gray-400 hover:text-primary transition-colors">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button type="button" onclick="openEditLicense({{ json_encode(['id' => $license->id, 'site_url' => $license->site_url, 'status' => $license->status->value, 'billing_cycle' => $license->billing_cycle->value ?? $license->billing_cycle, 'monthly_rate' => $license->monthly_rate, 'next_billing_at' => $license->next_billing_at?->format('Y-m-d'), 'client_name' => $license->client->name, 'license_key' => $license->license_key, 'has_system_token' => !empty($license->system_token)]) }})" class="text-gray-400 hover:text-accent transition-colors">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button type="button" onclick="openToggleModal({{ json_encode(['id' => $license->id, 'license_key' => $license->license_key, 'client_name' => $license->client->name, 'site_url' => $license->site_url, 'system_enabled' => $license->system_enabled, 'has_system_token' => !empty($license->system_token)]) }})" class="transition-colors {{ $license->system_enabled === true ? 'text-emerald-500 hover:text-emerald-700' : ($license->system_enabled === false ? 'text-red-400 hover:text-red-600' : 'text-gray-300 hover:text-gray-500') }}">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                No se encontraron licencias activas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($licenses->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $licenses->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Nueva Licencia -->
    <div id="createLicenseModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="document.getElementById('createLicenseModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-2xl sm:w-full mx-auto">
                <form method="POST" action="{{ route('licenses.store') }}">
                    @csrf
                    <div class="px-8 py-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900">Nueva Licencia MikPoS</h3>
                            <button type="button" onclick="document.getElementById('createLicenseModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="px-8 py-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                            <select name="client_id" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                <option value="">Selecciona un cliente</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->client_type->label() }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL del Sitio Web</label>
                            <input type="url" name="site_url" placeholder="https://ejemplo.com" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ciclo de Facturación *</label>
                                <select name="billing_cycle" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                    @foreach($billingCycles as $cycle)
                                        <option value="{{ $cycle->value }}">{{ $cycle->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tarifa Mensual ($) *</label>
                                <input type="number" step="0.01" name="monthly_rate" value="50000.00" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Primer Pago *</label>
                            <input type="date" name="next_billing_at" value="{{ now()->format('Y-m-d') }}" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>
                        <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm text-blue-700">
                                <strong>Nota:</strong> Si el cliente es revendedor y esta es su 5ta licencia, se marcará automáticamente como <strong>Gratuita</strong>.
                            </p>
                        </div>
                    </div>
                    <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-3 rounded-b-2xl">
                        <button type="button" onclick="document.getElementById('createLicenseModal').classList.add('hidden')" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancelar</button>
                        <button type="submit" class="px-6 py-2 bg-accent text-white text-sm font-bold rounded-xl hover:bg-accent/90 transition-colors shadow-sm shadow-accent/20">Activar Licencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($errors->any())
    <script>document.addEventListener('DOMContentLoaded', () => document.getElementById('createLicenseModal').classList.remove('hidden'));</script>
    @endif

    <!-- Modal: Editar Licencia -->
    <div id="editLicenseModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="document.getElementById('editLicenseModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-auto">
                <form id="editLicenseForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="px-8 py-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Editar Licencia</h3>
                                <p class="text-sm text-gray-500 mt-1" id="editLicenseSubtitle"></p>
                            </div>
                            <button type="button" onclick="document.getElementById('editLicenseModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="px-8 py-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL del Sitio Web</label>
                            <input type="url" name="site_url" id="editLicenseSiteUrl" placeholder="https://ejemplo.com" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Token de Sistema
                                <span class="text-gray-400 font-normal text-xs">(SYSTEM_ADMIN_TOKEN del sitio)</span>
                            </label>
                            <p id="editLicenseTokenHint" class="hidden mb-1 text-xs text-emerald-600 font-medium">Token configurado &#10003; &mdash; ingresa uno nuevo para reemplazarlo.</p>
                            <input type="password" name="system_token" id="editLicenseToken" value="" placeholder="Token secreto del sitio MikPOS" autocomplete="new-password" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            <p class="mt-1 text-xs text-gray-400">Déjalo vacío para no modificarlo.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="status" id="editLicenseStatus" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                    @foreach(\App\Enums\LicenseStatus::cases() as $status)
                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ciclo de Facturación</label>
                                <select name="billing_cycle" id="editLicenseCycle" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                                    @foreach($billingCycles as $cycle)
                                        <option value="{{ $cycle->value }}">{{ $cycle->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tarifa Mensual ($)</label>
                                <input type="number" step="0.01" name="monthly_rate" id="editLicenseRate" required class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Próxima Facturación</label>
                                <input type="date" name="next_billing_at" id="editLicenseBilling" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-accent focus:ring-accent sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-3 rounded-b-2xl">
                        <button type="button" onclick="document.getElementById('editLicenseModal').classList.add('hidden')" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancelar</button>
                        <button type="submit" class="px-6 py-2 bg-accent text-white text-sm font-bold rounded-xl hover:bg-accent/90 transition-colors shadow-sm shadow-accent/20">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openEditLicense(l) {
        document.getElementById('editLicenseForm').action = '/licenses/' + l.id;
        document.getElementById('editLicenseSubtitle').textContent = l.client_name + ' — ' + (l.license_key || '');
        document.getElementById('editLicenseSiteUrl').value = l.site_url || '';
        document.getElementById('editLicenseStatus').value = l.status || '';
        document.getElementById('editLicenseCycle').value = l.billing_cycle || '';
        document.getElementById('editLicenseRate').value = l.monthly_rate || '';
        document.getElementById('editLicenseBilling').value = l.next_billing_at || '';
        document.getElementById('editLicenseToken').value = '';
        const hint = document.getElementById('editLicenseTokenHint');
        if (l.has_system_token) {
            hint.classList.remove('hidden');
        } else {
            hint.classList.add('hidden');
        }
        document.getElementById('editLicenseModal').classList.remove('hidden');
    }

    function openShowLicense(l) {
        const m = document.getElementById('showLicenseModal');
        // Header
        document.getElementById('showLicenseKey').textContent = l.license_key || '';
        const statusBadge = document.getElementById('showLicenseStatus');
        statusBadge.textContent = l.status;
        statusBadge.className = 'px-2.5 py-1 text-xs font-medium rounded-full bg-' + l.status_color + '-100 text-' + l.status_color + '-700';
        const freeBadge = document.getElementById('showLicenseFreeBadge');
        freeBadge.classList.toggle('hidden', !l.is_free_promotion);

        // Details
        document.getElementById('showClientName').textContent = l.client_name;
        document.getElementById('showClientType').textContent = l.client_type;
        const urlEl = document.getElementById('showSiteUrl');
        if (l.site_url) {
            urlEl.innerHTML = '<a href="' + l.site_url + '" target="_blank" class="text-indigo-600 hover:text-indigo-800 break-all">' + l.site_url + '</a>';
        } else {
            urlEl.innerHTML = '<span class="text-gray-400">No registrada</span>';
        }
        document.getElementById('showCycle').textContent = l.billing_cycle;
        document.getElementById('showRate').textContent = '$' + Number(l.monthly_rate).toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        document.getElementById('showInstallation').textContent = '$' + Number(l.installation_fee).toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        document.getElementById('showCycleAmount').textContent = '$' + Number(l.cycle_amount).toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        document.getElementById('showActivated').textContent = l.activated_at || '—';
        document.getElementById('showNextBilling').textContent = l.next_billing_at || '—';

        // Financial
        const totalCycle = Number(l.installation_fee) + Number(l.cycle_amount);
        document.getElementById('showTotalCycle').textContent = '$' + totalCycle.toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        document.getElementById('showFinCycleAmount').textContent = '$' + Number(l.cycle_amount).toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        document.getElementById('showFinInstallation').textContent = '$' + Number(l.installation_fee).toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0});

        // Reseller summary
        const resellerSection = document.getElementById('showResellerSection');
        if (l.is_reseller && l.reseller_summary) {
            resellerSection.classList.remove('hidden');
            const s = l.reseller_summary;
            document.getElementById('showResellerTotal').textContent = s.total_licenses;
            document.getElementById('showResellerPaid').textContent = s.paid_licenses;
            document.getElementById('showResellerFree').textContent = s.free_licenses;
            const nextEl = document.getElementById('showResellerNext');
            if (s.next_license_is_free) {
                nextEl.textContent = '🎁 ¡SERÁ GRATIS!';
                nextEl.className = 'text-sm font-bold text-green-600';
            } else {
                nextEl.textContent = 'Faltan ' + s.remaining_for_next_free + ' para la siguiente gratis';
                nextEl.className = 'text-sm font-bold text-purple-700';
            }
        } else {
            resellerSection.classList.add('hidden');
        }

        m.classList.remove('hidden');

        // System status in footer
        const sysEl = document.getElementById('showSystemStatus');
        if (l.system_enabled === true) {
            sysEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span><span class="text-emerald-700 font-medium">Sistema habilitado</span>';
        } else if (l.system_enabled === false) {
            sysEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span><span class="text-red-600 font-medium">Sistema deshabilitado</span>';
        } else {
            sysEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span><span class="text-gray-400">Estado del sistema desconocido</span>';
        }
    }

    function openToggleModal(l) {
        document.getElementById('toggleModalSubtitle').textContent = l.client_name + ' — ' + l.license_key;
        document.getElementById('toggleSystemForm').action = '/licenses/' + l.id + '/toggle-system';

        const statusBox  = document.getElementById('toggleStatusBox');
        const statusLabel = document.getElementById('toggleStatusLabel');
        const statusDot  = document.getElementById('toggleStatusDot');
        const noConfig   = document.getElementById('toggleNoConfig');
        const buttons    = document.getElementById('toggleButtons');
        const btnDisable = document.getElementById('btnDisable');
        const btnEnable  = document.getElementById('btnEnable');

        if (l.system_enabled === true) {
            statusLabel.textContent = 'Habilitado';
            statusLabel.className = 'text-sm font-semibold text-emerald-700';
            statusDot.className = 'w-3 h-3 rounded-full bg-emerald-500';
            statusBox.className = 'flex items-center justify-between p-4 rounded-xl border border-emerald-200 bg-emerald-50';
        } else if (l.system_enabled === false) {
            statusLabel.textContent = 'Deshabilitado';
            statusLabel.className = 'text-sm font-semibold text-red-600';
            statusDot.className = 'w-3 h-3 rounded-full bg-red-500';
            statusBox.className = 'flex items-center justify-between p-4 rounded-xl border border-red-200 bg-red-50';
        } else {
            statusLabel.textContent = 'Desconocido — usa "Consultar" para verificar';
            statusLabel.className = 'text-sm font-semibold text-gray-500';
            statusDot.className = 'w-3 h-3 rounded-full bg-gray-400';
            statusBox.className = 'flex items-center justify-between p-4 rounded-xl border border-gray-200 bg-gray-50';
        }

        if (!l.site_url || !l.has_system_token) {
            noConfig.classList.remove('hidden');
            buttons.classList.add('hidden');
        } else {
            noConfig.classList.add('hidden');
            buttons.classList.remove('hidden');
            btnDisable.disabled = (l.system_enabled === false);
            btnEnable.disabled  = (l.system_enabled === true);
        }

        document.getElementById('toggleSystemModal').classList.remove('hidden');
    }

    function submitToggle(action) {
        document.getElementById('toggleSystemAction').value = action;
        document.getElementById('toggleSystemForm').submit();
    }
    </script>

    <!-- Modal: Ver Licencia -->
    <div id="showLicenseModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="document.getElementById('showLicenseModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-4xl sm:w-full mx-auto">
                <!-- Header -->
                <div class="px-8 py-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <h3 class="text-xl font-bold text-gray-900 font-mono" id="showLicenseKey"></h3>
                            <span id="showLicenseStatus" class="px-2.5 py-1 text-xs font-medium rounded-full"></span>
                            <span id="showLicenseFreeBadge" class="hidden px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">🎁 Gratis</span>
                        </div>
                        <button type="button" onclick="document.getElementById('showLicenseModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <!-- Body -->
                <div class="px-8 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- License Details -->
                        <div class="bg-gray-50 rounded-xl p-5">
                            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Detalles de Licencia</h4>
                            <dl class="space-y-2">
                                <div><dt class="text-xs text-gray-400">Cliente</dt><dd class="text-sm font-medium text-gray-900" id="showClientName"></dd></div>
                                <div><dt class="text-xs text-gray-400">Tipo Cliente</dt><dd class="text-sm font-medium text-gray-900" id="showClientType"></dd></div>
                                <div><dt class="text-xs text-gray-400">URL Sitio Web</dt><dd class="text-sm font-medium" id="showSiteUrl"></dd></div>
                                <div><dt class="text-xs text-gray-400">Ciclo</dt><dd class="text-sm font-medium text-gray-900" id="showCycle"></dd></div>
                                <div><dt class="text-xs text-gray-400">Tarifa Mensual</dt><dd class="text-sm font-medium text-gray-900" id="showRate"></dd></div>
                                <div><dt class="text-xs text-gray-400">Costo Instalación</dt><dd class="text-sm font-medium text-gray-900" id="showInstallation"></dd></div>
                                <div><dt class="text-xs text-gray-400">Valor Ciclo</dt><dd class="text-sm font-bold text-gray-900" id="showCycleAmount"></dd></div>
                                <div><dt class="text-xs text-gray-400">Activada</dt><dd class="text-sm font-medium text-gray-900" id="showActivated"></dd></div>
                                <div><dt class="text-xs text-gray-400">Próx. Facturación</dt><dd class="text-sm font-medium text-gray-900" id="showNextBilling"></dd></div>
                            </dl>
                        </div>

                        <!-- Financial Summary -->
                        <div class="bg-gray-50 rounded-xl p-5">
                            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Resumen de Licencia</h4>
                            <div class="space-y-4">
                                <div class="text-center p-4 bg-white rounded-lg border border-gray-200">
                                    <p class="text-xs text-gray-400">Valor Total por Ciclo</p>
                                    <p class="text-3xl font-bold text-blue-600" id="showTotalCycle"></p>
                                </div>
                                <div class="grid grid-cols-2 gap-3 text-center">
                                    <div class="p-3 bg-blue-50 rounded-lg">
                                        <p class="text-xs text-blue-500">Tarifa Ciclo</p>
                                        <p class="text-lg font-bold text-blue-700" id="showFinCycleAmount"></p>
                                    </div>
                                    <div class="p-3 bg-emerald-50 rounded-lg">
                                        <p class="text-xs text-emerald-500">Instalación</p>
                                        <p class="text-lg font-bold text-emerald-700" id="showFinInstallation"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reseller Summary -->
                        <div id="showResellerSection" class="hidden bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl border border-purple-200 p-5">
                            <h4 class="text-sm font-semibold text-purple-600 uppercase mb-3">📊 Resumen Revendedor</h4>
                            <dl class="space-y-2">
                                <div><dt class="text-xs text-purple-400">Total Licencias</dt><dd class="text-sm font-bold text-purple-700" id="showResellerTotal"></dd></div>
                                <div><dt class="text-xs text-purple-400">Licencias Pagadas</dt><dd class="text-sm font-bold text-purple-700" id="showResellerPaid"></dd></div>
                                <div><dt class="text-xs text-purple-400">Licencias Gratis</dt><dd class="text-sm font-bold text-green-600" id="showResellerFree"></dd></div>
                                <div class="pt-2 border-t border-purple-200">
                                    <dt class="text-xs text-purple-400">Próxima Licencia</dt>
                                    <dd id="showResellerNext" class="text-sm font-bold text-purple-700"></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between rounded-b-2xl">
                    <div id="showSystemStatus" class="flex items-center gap-2 text-sm"></div>
                    <button type="button" onclick="document.getElementById('showLicenseModal').classList.add('hidden')" class="px-6 py-2 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary/90 transition-colors">Cerrar</button>
                </div>
            </div>
        </div>
    </div>    <!-- Modal: Control del Sistema -->
    <div id="toggleSystemModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="document.getElementById('toggleSystemModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-md sm:w-full mx-auto">
                <div class="px-8 py-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Control del Sistema</h3>
                            <p class="text-sm text-gray-500 mt-1" id="toggleModalSubtitle"></p>
                        </div>
                        <button type="button" onclick="document.getElementById('toggleSystemModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="px-8 py-6 space-y-5">
                    <div class="flex items-center justify-between p-4 rounded-xl border" id="toggleStatusBox">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Estado actual del sistema</p>
                            <p class="text-sm font-semibold" id="toggleStatusLabel"></p>
                        </div>
                        <span class="w-3 h-3 rounded-full" id="toggleStatusDot"></span>
                    </div>
                    <div id="toggleNoConfig" class="hidden rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-700">
                        Para controlar este sistema, configura la <strong>URL del sitio</strong> y el <strong>Token de Sistema</strong> en el ícono de editar (lápiz).
                    </div>
                    <div id="toggleButtons" class="hidden flex gap-3">
                        <button type="button" id="btnDisable" onclick="submitToggle('disable')" class="flex-1 px-4 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition-colors">
                            Deshabilitar
                        </button>
                        <button type="button" id="btnEnable" onclick="submitToggle('enable')" class="flex-1 px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                            Habilitar
                        </button>
                        <button type="button" onclick="submitToggle('')" class="px-4 py-2.5 border border-gray-300 text-gray-600 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors" title="Consultar estado actual">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form oculto para el toggle (se envía via JS) -->
    <form id="toggleSystemForm" method="POST" action="" class="hidden">
        @csrf
        <input type="hidden" id="toggleSystemAction" name="action" value="">
    </form>

</x-app-layout>