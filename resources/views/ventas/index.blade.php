<x-app-layout>
    <x-clientes-styles />

    @php
        $estadoOptions = ['todos' => 'Todos', 'pendiente' => 'Pendiente', 'abono' => 'Con abono', 'pagada' => 'Pagada'];
        $estadoBadges = ['pendiente' => 'badge-orange', 'abono' => 'badge-blue', 'pagada' => 'badge-green'];
        $statsTone = ['blue' => 'icon-blue', 'green' => 'icon-green', 'purple' => 'icon-purple', 'orange' => 'icon-orange', 'teal' => 'icon-teal'];
        $salePayloads = $ventas->getCollection()->mapWithKeys(function ($venta) {
            $pagado = (float) ($venta->pagos_sum_valor ?? 0);
            $saldo = max(0, (float) $venta->total - $pagado);
            $clienteNombre = trim($venta->cliente->nombres . ' ' . ($venta->cliente->apellidos ?? ''));
            $vehiculoNombre = trim($venta->vehiculo->marca . ' ' . $venta->vehiculo->modelo . ' ' . ($venta->vehiculo->anio ?? ''));

            return [$venta->id => [
                'id' => $venta->id,
                'invoice' => 'FAC-' . str_pad((string) $venta->id, 5, '0', STR_PAD_LEFT),
                'cliente' => $clienteNombre,
                'documento' => $venta->cliente->documento,
                'vehiculo' => $vehiculoNombre,
                'placa' => $venta->vehiculo->placa,
                'color' => $venta->vehiculo->color ?? 'Sin color',
                'kilometraje' => $venta->vehiculo->kilometraje ? number_format((int) $venta->vehiculo->kilometraje, 0, ',', '.') . ' km' : 'Sin km',
                                    'fecha' => $venta->fecha_venta?->format('d/m/Y'),
                                    'precio_base' => '$' . number_format((float) $venta->precio_base, 0, ',', '.'),
                                    'descuento' => '$' . number_format((float) $venta->descuento, 0, ',', '.'),
                                    'impuestos' => '$' . number_format((float) $venta->impuestos, 0, ',', '.'),
                                    'total' => '$' . number_format((float) $venta->total, 0, ',', '.'),
                                    'pagado' => '$' . number_format($pagado, 0, ',', '.'),
                                    'saldo' => '$' . number_format($saldo, 0, ',', '.'),
                'saldo_raw' => $saldo,
                'estado' => ucfirst($venta->estado),
                'estado_raw' => $venta->estado,
                'vendedor' => $venta->vendedor?->name ?? 'Sin asignar',
                'notas' => $venta->notas ?: 'Sin notas registradas.',
                'image' => $venta->vehiculo->imagen ? route('vehiculos.imagen', $venta->vehiculo) : null,
                'show_url' => route('ventas.show', $venta),
                'edit_url' => route('ventas.edit', $venta),
            ]];
        });
        $selectedSale = $salePayloads->first() ?? [];
    @endphp

    <section class="sales-page" x-data="{
        createOpen: {{ $errors->any() ? 'true' : 'false' }},
        showOpen: false,
        abonoOpen: false,
        facturaOpen: false,
        selectedSales: [],
        selectAll: false,
        query: @js(request()->query()),
        selected: @js($selectedSale),
        exportHref() {
            const params = new URLSearchParams(this.query);
            if (this.selectedSales.length) {
                params.set('ids', this.selectedSales.join(','));
            } else {
                params.delete('ids');
            }
            return `{{ route('ventas.exportar') }}?${params.toString()}`;
        },
        toggleAll(checked, ids) {
            this.selectAll = checked;
            this.selectedSales = checked ? ids.map(String) : [];
        },
        syncSelectAll(ids) {
            const normalized = ids.map(String);
            this.selectAll = normalized.length > 0 && normalized.every(id => this.selectedSales.includes(id));
        },
        open(type, venta) {
            this.selected = venta;
            this.showOpen = type === 'show';
            this.abonoOpen = type === 'abono';
            this.facturaOpen = type === 'factura';
        }
    }">
        <div class="sales-hero">
            <div>
                <span class="sales-eyebrow">Modulo comercial</span>
                <h1 class="page-title">Ventas</h1>
                <p class="page-subtitle">Control de cierres, recaudo, facturacion y cartera de vehiculos.</p>
            </div>
            <div class="sales-actions">
                <a :href="exportHref()" class="btn-export sales-action-ghost">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4v10m0 0 4-4m-4 4-4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 17v2h14v-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <span>Exportar Excel</span>
                </a>
                <button class="sales-action-ghost" type="button" @click="open('abono', selected)" :disabled="!selected.id">Registrar abono</button>
                <button class="sales-action-ghost" type="button" @click="open('factura', selected)" :disabled="!selected.id">Generar factura</button>
                <a href="{{ route('ventas.create') }}" @click.prevent="createOpen = true" class="btn-new-sale">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <span>Nueva venta</span>
                </a>
            </div>
        </div>

        <section class="sales-kpi-grid">
            @foreach ($stats as $stat)
                <article class="sale-kpi-card">
                    <div class="sale-kpi-card__top">
                        <div class="stat-icon {{ $statsTone[$stat['tone']] ?? 'icon-blue' }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M6 7l1.2 12h9.6L18 7M9 11h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <span>{{ $stat['label'] }}</span>
                    </div>
                    <strong>{{ $stat['value'] }}</strong>
                    <small>{{ $stat['trend'] }}</small>
                </article>
            @endforeach
        </section>

        <form method="GET" action="{{ route('ventas.index') }}" class="sales-filter-bar">
            <div class="filter-input-wrap sales-search">
                <input class="filter-input" type="search" name="q" value="{{ $search }}" placeholder="Buscar factura, cliente, documento, vehiculo o placa...">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            </div>
            <select class="filter-select" name="estado" onchange="this.form.submit()">
                @foreach ($estadoOptions as $value => $label)
                    <option value="{{ $value }}" @selected($estado === $value)>Estado: {{ $label }}</option>
                @endforeach
            </select>
            <select class="filter-select" name="asesor" disabled>
                <option>Todos los asesores</option>
            </select>
            <input class="filter-input" type="date" name="desde" value="{{ $desde }}" onchange="this.form.submit()" aria-label="Desde">
            <input class="filter-input" type="date" name="hasta" value="{{ $hasta }}" onchange="this.form.submit()" aria-label="Hasta">
            <button class="btn-filters" type="submit">Filtrar</button>
        </form>

        <div class="sale-export-selection">
            <input type="checkbox" :checked="selectAll" @change="toggleAll($event.target.checked, @js($ventas->pluck('id')->all()))">
            <span>Selecciona una o varias ventas para exportar solo esos registros</span>
        </div>

        @if (session('status'))
            <div class="crud-alert">{{ session('status') }}</div>
        @endif

        <div class="sales-dashboard-grid">
            <section class="sales-table-panel">
                <div class="panel-head">
                    <div>
                        <h2>Operaciones comerciales</h2>
                        <p>{{ number_format($ventas->total(), 0, ',', '.') }} registros en cartera y facturacion.</p>
                    </div>
                    <span class="panel-chip">Vista ejecutiva</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th style="width:44px;"><input type="checkbox" :checked="selectAll" @change="toggleAll($event.target.checked, @js($ventas->pluck('id')->all()))"></th>
                                <th>Factura</th>
                                <th>Cliente</th>
                                <th>Vehiculo</th>
                                <th>Placa</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Pagado</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                                <th>Asesor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ventas as $venta)
                                @php $payload = $salePayloads->get($venta->id); @endphp
                                <tr @click="selected = @js($payload)" :class="selected.id === {{ $venta->id }} ? 'is-selected' : ''">
                                    <td style="width:44px;" @click.stop>
                                        <input type="checkbox" :value="{{ $venta->id }}" x-model="selectedSales" @change="syncSelectAll(@js($ventas->pluck('id')->all()))" style="width:16px;height:16px;accent-color:#3B82F6;">
                                    </td>
                                    <td><strong>{{ $payload['invoice'] }}</strong><small>Venta #{{ $venta->id }}</small></td>
                                    <td>{{ $payload['cliente'] }}<small>{{ $payload['documento'] }}</small></td>
                                    <td><strong>{{ $payload['vehiculo'] }}</strong><small>{{ $payload['color'] }} · {{ $payload['kilometraje'] }}</small></td>
                                    <td><span class="plate-pill">{{ $payload['placa'] ?? 'Sin placa' }}</span></td>
                                    <td>{{ $payload['fecha'] }}</td>
                                    <td>{{ $payload['total'] }}</td>
                                    <td>{{ $payload['pagado'] }}</td>
                                    <td>{{ $payload['saldo'] }}</td>
                                    <td><span class="badge {{ $estadoBadges[$venta->estado] ?? 'badge-blue' }}">{{ ucfirst($venta->estado) }}</span></td>
                                    <td>{{ $payload['vendedor'] }}</td>
                                    <td>
                                        <div class="action-buttons" @click.stop>
                                            <button class="action-btn view" type="button" @click="open('show', @js($payload))" aria-label="Ver venta"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.8 12S6.2 5.5 12 5.5 21.2 12 21.2 12 17.8 18.5 12 18.5 2.8 12 2.8 12Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="2.8" stroke="currentColor" stroke-width="1.7"/></svg></button>
                                            <a class="action-btn edit" href="{{ route('ventas.edit', $venta) }}" aria-label="Editar venta"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0 0-3l-.5-.5a2.1 2.1 0 0 0-3 0l-10 10L4 20Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m14.5 6.5 3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></a>
                                            <button class="action-btn pay" type="button" @click="open('abono', @js($payload))" aria-label="Registrar abono"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16v10H4z" stroke="currentColor" stroke-width="1.7"/><path d="M8 12h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></button>
                                            <button class="action-btn invoice" type="button" @click="open('factura', @js($payload))" aria-label="Generar factura"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 3h10v18l-2-1-2 1-2-1-2 1-2-1V3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 8h6M9 12h6M9 16h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="12" class="empty-table">No hay ventas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-footer sales-table-footer">
                    <div>Mostrando {{ $ventas->firstItem() ?? 0 }} a {{ $ventas->lastItem() ?? 0 }} de {{ number_format($ventas->total(), 0, ',', '.') }} ventas</div>
                    <div class="pagination">
                        <a href="{{ $ventas->previousPageUrl() ?: '#' }}" class="page-btn {{ $ventas->onFirstPage() ? 'disabled' : '' }}">Anterior</a>
                        @for ($page = 1; $page <= min(5, $ventas->lastPage()); $page++)
                            <a href="{{ $ventas->url($page) }}" class="page-btn {{ $ventas->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>
                        @endfor
                        <a href="{{ $ventas->nextPageUrl() ?: '#' }}" class="page-btn {{ $ventas->hasMorePages() ? '' : 'disabled' }}">Siguiente</a>
                    </div>
                </div>
            </section>

            @include('ventas.partials.dashboard-side')
        </div>

        @include('ventas.partials.bottom-panels')
        @include('ventas.partials.create-modal')
        @include('ventas.partials.show-modal')
        @include('ventas.partials.abono-modal')
        @include('ventas.partials.factura-modal')
    </section>

    @push('styles')
        <style>
            .sales-page{padding:22px 28px 34px;color:#F8FAFC}.sales-hero{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:18px}.sales-eyebrow{display:inline-flex;margin-bottom:8px;color:#60A5FA;font-size:12px;font-weight:800;letter-spacing:.16em;text-transform:uppercase}.sales-actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}.btn-new-sale{height:42px;padding:0 18px;border-radius:10px;background:linear-gradient(90deg,#2563EB,#7C3AED);color:#fff;font-size:13px;font-weight:800;display:inline-flex;align-items:center;gap:9px;box-shadow:0 16px 30px rgba(37,99,235,.26);text-decoration:none}.btn-new-sale svg{width:16px;height:16px}.sales-action-ghost{height:42px;padding:0 14px;border-radius:10px;background:rgba(15,23,42,.78);border:1px solid rgba(148,163,184,.18);color:#CBD5E1;font-size:13px;font-weight:800;display:inline-flex;align-items:center;gap:8px;text-decoration:none}.sales-action-ghost:disabled{opacity:.45;cursor:not-allowed}.sales-kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:14px}.sale-kpi-card{min-height:132px;border-radius:16px;padding:16px;background:linear-gradient(180deg,rgba(30,41,59,.94),rgba(15,23,42,.98));border:1px solid rgba(148,163,184,.15);box-shadow:0 18px 42px rgba(0,0,0,.22);position:relative;overflow:hidden}.sale-kpi-card:after{content:"";position:absolute;inset:auto -30px -46px auto;width:130px;height:130px;border-radius:999px;background:rgba(59,130,246,.10)}.sale-kpi-card__top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px}.sale-kpi-card__top span{font-size:13px;font-weight:800;color:#CBD5E1}.sale-kpi-card strong{display:block;font-size:27px;line-height:1;color:#fff;letter-spacing:-.04em}.sale-kpi-card small{display:block;margin-top:10px;color:#94A3B8;font-size:12px}.sales-filter-bar{display:grid;grid-template-columns:minmax(260px,2fr) .8fr .9fr .72fr .72fr auto;gap:10px;padding:12px;border-radius:16px;background:rgba(8,17,31,.82);border:1px solid rgba(148,163,184,.15);box-shadow:0 14px 34px rgba(0,0,0,.18);margin-bottom:14px;align-items:center}.sales-search .filter-input{padding-right:40px}.sales-dashboard-grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:14px;align-items:start}.sales-table-panel,.sales-side-card,.sales-bottom-card{border-radius:18px;background:linear-gradient(180deg,rgba(30,41,59,.94),rgba(8,17,31,.98));border:1px solid rgba(148,163,184,.15);box-shadow:0 20px 48px rgba(0,0,0,.24);overflow:hidden}.panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:16px 18px;border-bottom:1px solid rgba(148,163,184,.12)}.panel-head h2{font-size:16px;font-weight:900;color:#fff}.panel-head p{margin-top:4px;color:#94A3B8;font-size:12px}.panel-chip{height:26px;padding:0 10px;border-radius:999px;background:rgba(59,130,246,.14);color:#93C5FD;font-size:11px;font-weight:900;display:inline-flex;align-items:center;white-space:nowrap}.sales-table{width:100%;border-collapse:collapse;min-width:1220px}.sales-table thead{background:rgba(2,6,23,.45)}.sales-table th{padding:12px 14px;text-align:left;color:#94A3B8;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}.sales-table td{padding:13px 14px;color:#CBD5E1;font-size:13px;border-top:1px solid rgba(148,163,184,.10);vertical-align:middle}.sales-table tbody tr{cursor:pointer}.sales-table tbody tr:hover,.sales-table tbody tr.is-selected{background:rgba(59,130,246,.08)}.sales-table strong{display:block;color:#F8FAFC;font-size:13px}.sales-table small{display:block;color:#64748B;margin-top:3px;font-size:11px}.plate-pill{display:inline-flex;height:28px;align-items:center;padding:0 10px;border-radius:7px;background:rgba(15,23,42,.92);border:1px solid rgba(148,163,184,.16);color:#E2E8F0;font-weight:900;letter-spacing:.08em}.badge-orange{color:#FDBA74;background:rgba(249,115,22,.18)}.action-btn.pay{color:#22C55E}.action-btn.invoice{color:#C084FC}.empty-table{padding:22px 16px!important;color:#94A3B8!important}.sales-table-footer{padding:14px 18px;border-top:1px solid rgba(148,163,184,.10)}.sales-side-stack{display:grid;gap:14px}.sales-side-card{padding:16px}.side-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}.side-title h3{font-size:15px;font-weight:900;color:#fff}.side-title span{font-size:11px;color:#64748B;font-weight:800;text-transform:uppercase;letter-spacing:.1em}.day-grid{display:grid;grid-template-columns:1fr;gap:10px}.day-card{padding:12px;border-radius:14px;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.12)}.day-card span{display:block;color:#94A3B8;font-size:12px}.day-card strong{display:block;margin-top:6px;color:#fff;font-size:19px}.day-card small{display:block;margin-top:3px;color:#64748B}.selected-vehicle{height:160px;border-radius:14px;background:linear-gradient(135deg,rgba(37,99,235,.18),rgba(124,58,237,.12));border:1px solid rgba(148,163,184,.14);display:grid;place-items:center;overflow:hidden;margin-bottom:12px}.selected-vehicle img{width:100%;height:100%;object-fit:cover}.selected-vehicle svg{width:66px;height:66px;color:#60A5FA}.detail-list{display:grid;gap:9px}.detail-line{display:flex;justify-content:space-between;gap:12px;padding-bottom:9px;border-bottom:1px solid rgba(148,163,184,.10);font-size:12px;color:#94A3B8}.detail-line strong{color:#F8FAFC;text-align:right}.side-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:14px}.collection-list,.activity-list{display:grid;gap:10px}.collection-item,.activity-item{display:flex;justify-content:space-between;gap:12px;padding:11px;border-radius:14px;background:rgba(15,23,42,.62);border:1px solid rgba(148,163,184,.10)}.collection-item strong,.activity-item strong{display:block;color:#F8FAFC;font-size:13px}.collection-item span,.activity-item span{display:block;color:#94A3B8;font-size:12px;margin-top:3px}.collection-amount,.activity-amount{text-align:right;color:#FDBA74;font-weight:900;font-size:13px}.sales-bottom-grid{display:grid;grid-template-columns:.95fr 1.05fr;gap:14px;margin-top:14px}.sales-bottom-card{padding:16px}.process-steps{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.process-step{min-height:106px;padding:13px;border-radius:14px;background:rgba(15,23,42,.64);border:1px solid rgba(148,163,184,.12);position:relative}.process-step:before{content:attr(data-step);width:26px;height:26px;border-radius:9px;background:linear-gradient(135deg,#2563EB,#7C3AED);display:grid;place-items:center;color:#fff;font-size:12px;font-weight:900;margin-bottom:12px}.process-step strong{display:block;color:#fff;font-size:13px}.process-step span{display:block;color:#94A3B8;font-size:12px;margin-top:5px}.sale-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.sale-grid--3{grid-template-columns:repeat(3,minmax(0,1fr));margin-top:14px}.sale-field{display:grid;gap:8px}.sale-field--full{margin-top:14px}.sale-field label span{font-size:13px;font-weight:800;color:#E2E8F0}.sale-field input,.sale-field select,.sale-field textarea{height:42px;border-radius:10px;background:rgba(15,23,42,.90);border:1px solid rgba(148,163,184,.18);color:#E2E8F0;padding:0 14px;font-size:13px}.sale-field textarea{height:92px;padding:12px 14px;resize:vertical}.sale-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px;flex-wrap:wrap}.sale-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.sale-detail-grid div,.invoice-box{padding:12px;border-radius:12px;background:rgba(15,23,42,.64);border:1px solid rgba(148,163,184,.12)}.sale-detail-grid span,.invoice-line span{display:block;color:#94A3B8;font-size:12px}.sale-detail-grid strong,.invoice-line strong{display:block;margin-top:4px;color:#F8FAFC}.invoice-box h3{font-size:18px;font-weight:900;color:#fff;margin-bottom:12px}.invoice-line{display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-top:1px solid rgba(148,163,184,.10)}@media (max-width:1400px){.sales-dashboard-grid{grid-template-columns:1fr}.sales-side-stack{grid-template-columns:repeat(3,minmax(0,1fr))}.sales-filter-bar{grid-template-columns:1fr 1fr 1fr}.sales-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (max-width:1024px){.sales-page{padding:18px 16px 28px}.sales-hero{flex-direction:column}.sales-actions{justify-content:flex-start}.sales-side-stack,.sales-bottom-grid{grid-template-columns:1fr}.process-steps{grid-template-columns:repeat(2,minmax(0,1fr))}.sales-filter-bar{grid-template-columns:1fr 1fr}}@media (max-width:680px){.sales-kpi-grid,.sales-filter-bar,.process-steps,.sale-grid,.sale-grid--3,.sale-detail-grid{grid-template-columns:1fr}.side-actions{grid-template-columns:1fr}.sale-kpi-card strong{font-size:23px}}
        </style>
    @endpush

    @push('styles')
        <style>
            .sale-export-selection{display:flex;align-items:center;gap:10px;margin:0 0 14px;color:#CBD5E1;font-size:13px}
        </style>
    @endpush
</x-app-layout>
