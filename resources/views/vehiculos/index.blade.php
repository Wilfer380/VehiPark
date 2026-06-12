<x-app-layout>
    <x-clientes-styles />

    @php
        $tipoLabels = [
            'carro' => 'Carro',
            'moto' => 'Moto',
            'camioneta' => 'Camioneta',
            'camion' => 'Camión',
            'otro' => 'Otro',
            'automovil' => 'Carro',
            'motocicleta' => 'Moto',
        ];

        $tipoOptions = ['todos' => 'Todos', 'carro' => 'Carro', 'moto' => 'Moto', 'camioneta' => 'Camioneta', 'camion' => 'Camión', 'otro' => 'Otro'];
        $estadoOptions = ['todos' => 'Todos', 'disponible' => 'Disponible', 'vendido' => 'Vendido', 'reservado' => 'Reservado', 'mantenimiento' => 'Mantenimiento', 'parqueado' => 'Parqueado', 'inactivo' => 'Inactivo'];
        $yearOptions = ['todos' => 'Todos', '2024' => '2024', '2023' => '2023', '2022' => '2022', '2021' => '2021', '2020' => '2020', 'anteriores' => 'Anteriores'];

        $stateBadges = [
            'disponible' => 'badge-green',
            'vendido' => 'badge-purple',
            'reservado' => 'badge-blue',
            'mantenimiento' => 'badge-orange',
            'parqueado' => 'badge-yellow',
            'inactivo' => 'badge-red',
        ];

        $statsTone = [
            'blue' => 'icon-blue',
            'green' => 'icon-green',
            'purple' => 'icon-purple',
            'orange' => 'icon-orange',
            'teal' => 'icon-teal',
        ];

        $ubicacionLabels = [
            'inventario venta' => 'Inventario venta',
            'parqueadero' => 'Parqueadero',
            'taller' => 'Taller',
            'vendido' => 'Vendido',
            'reservado' => 'Reservado',
            '0' => 'Inventario venta',
            '1' => 'Parqueadero',
            '2' => 'Taller',
            '3' => 'Vendido',
            '4' => 'Reservado',
        ];
    @endphp

    <section class="vehicles-page" x-data="{
        selectedVehicles: [],
        selectAll: false,
        query: @js(request()->query()),
        exportHref() {
            const params = new URLSearchParams(this.query);
            if (this.selectedVehicles.length) {
                params.set('ids', this.selectedVehicles.join(','));
            } else {
                params.delete('ids');
            }
            return `{{ route('vehiculos.exportar') }}?${params.toString()}`;
        },
        toggleAll(checked, ids) {
            this.selectAll = checked;
            this.selectedVehicles = checked ? ids.map(String) : [];
        },
        syncSelectAll(ids) {
            const normalized = ids.map(String);
            this.selectAll = normalized.length > 0 && normalized.every(id => this.selectedVehicles.includes(id));
        }
    }">
        <div class="vehicles-header">
            <div>
                <h1 class="page-title">Vehículos</h1>
                <p class="page-subtitle">Gestiona el inventario de vehículos y su estado en el sistema</p>
            </div>
            <a href="{{ route('vehiculos.create') }}" class="btn-new-vehicle">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span>+ Nuevo vehículo</span>
            </a>
        </div>

        <section class="vehicle-stats-grid">
            @foreach ($stats as $stat)
                <article class="vehicle-stat-card">
                    <div class="client-stat-card__top">
                        <div class="stat-icon {{ $statsTone[$stat['tone']] ?? 'icon-blue' }}">
                            @if ($stat['icon'] === 'car')
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 14h14l-1.2-4.2A2 2 0 0 0 16.9 8H7.1a2 2 0 0 0-1.9 1.8L5 14Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="8" cy="17" r="1.4" stroke="currentColor" stroke-width="1.7"/><circle cx="16" cy="17" r="1.4" stroke="currentColor" stroke-width="1.7"/></svg>
                            @elseif ($stat['icon'] === 'tag')
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 13.5 12.5 21 4 12.5V4h8.5L20 11.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="9" cy="9" r="1.2" fill="currentColor"/></svg>
                            @elseif ($stat['icon'] === 'cart')
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 5h2l2.4 10.2A2 2 0 0 0 9.4 17H18a2 2 0 0 0 1.9-1.4L22 8H6.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="20" r="1.4" fill="currentColor"/><circle cx="17" cy="20" r="1.4" fill="currentColor"/></svg>
                            @elseif ($stat['icon'] === 'parking')
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 20A8 8 0 1 0 4 12a8 8 0 0 0 8 8Z" stroke="currentColor" stroke-width="1.7"/><path d="M9.5 15V9h3.3a2.2 2.2 0 0 1 0 4.4H9.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4v16M4 12h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            @endif
                        </div>
                        <div class="stat-copy">
                            <div class="stat-label">{{ $stat['label'] }}</div>
                            <div class="stat-value">{{ $stat['value'] }}</div>
                        </div>
                    </div>
                    <div class="stat-trend">↑ {{ $stat['trend'] }}</div>
                </article>
            @endforeach
        </section>

        <form method="GET" action="{{ route('vehiculos.index') }}" class="vehicle-filters">
            <div class="filter-input-wrap">
                <input class="filter-input" type="search" name="q" value="{{ $search }}" placeholder="Buscar vehículos por placa, marca, modelo o color...">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            </div>

            <select class="filter-select" name="tipo" onchange="this.form.submit()">
                @foreach ($tipoOptions as $value => $label)
                    <option value="{{ $value }}" @selected($tipo === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select class="filter-select" name="estado" onchange="this.form.submit()">
                @foreach ($estadoOptions as $value => $label)
                    <option value="{{ $value }}" @selected($estado === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select class="filter-select" name="anio" onchange="this.form.submit()">
                @foreach ($yearOptions as $value => $label)
                    <option value="{{ $value }}" @selected($anio === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <a :href="exportHref()" class="btn-export">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4v10m0 0 4-4m-4 4-4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 17v2h14v-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span>Exportar Excel</span>
            </a>
        </form>

        <div class="vehicle-export-selection">
            <input type="checkbox" :checked="selectAll" @change="toggleAll($event.target.checked, @js($vehiculos->pluck('id')->all()))">
            <span>Selecciona uno o varios vehículos para exportar solo esos registros</span>
        </div>

        @if (session('status'))
            <div class="crud-alert">{{ session('status') }}</div>
        @endif

        <section class="vehicle-table-card">
            <div class="overflow-x-auto">
                <table class="vehicle-table">
                    <thead>
                        <tr>
                            <th style="width:44px;"><input type="checkbox" :checked="selectAll" @change="toggleAll($event.target.checked, @js($vehiculos->pluck('id')->all()))"></th>
                            <th>Vehículo</th>
                            <th>Placa</th>
                            <th>Tipo</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Año</th>
                            <th>Color</th>
                            <th>Kilometraje</th>
                            <th>Precio venta</th>
                            <th>Estado</th>
                            <th>Ubicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vehiculos as $vehiculo)
                            @php
                                $tipoLabel = $tipoLabels[$vehiculo->tipo] ?? ucfirst((string) $vehiculo->tipo);
                                $estadoClass = $stateBadges[$vehiculo->estado] ?? 'badge-blue';
                                $thumb = $vehiculo->imagen ? route('vehiculos.imagen', ['vehiculo' => $vehiculo, 'v' => optional($vehiculo->updated_at)->timestamp]) : null;
                            @endphp
                            <tr>
                                <td style="width:44px;">
                                    <input type="checkbox" :value="{{ $vehiculo->id }}" x-model="selectedVehicles" @change="syncSelectAll(@js($vehiculos->pluck('id')->all()))" style="width:16px;height:16px;accent-color:#3B82F6;">
                                </td>
                                <td>
                                    <div class="vehicle-info">
                                        @if ($thumb)
                                            <img src="{{ $thumb }}" class="vehicle-thumb" alt="Foto vehículo">
                                        @else
                                            <div class="vehicle-placeholder"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 14h14l-1.2-4.2A2 2 0 0 0 16.9 8H7.1a2 2 0 0 0-1.9 1.8L5 14Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="8" cy="17" r="1.4" stroke="currentColor" stroke-width="1.7"/><circle cx="16" cy="17" r="1.4" stroke="currentColor" stroke-width="1.7"/></svg></div>
                                        @endif
                                        <div>
                                            <div class="vehicle-name">{{ $vehiculo->marca }} {{ $vehiculo->modelo }} {{ $vehiculo->anio }}</div>
                                            <div class="vehicle-subtitle">{{ $tipoLabel }} · {{ $vehiculo->color ?? 'Sin color' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $vehiculo->placa }}</td>
                                <td>{{ $tipoLabel }}</td>
                                <td>{{ $vehiculo->marca }}</td>
                                <td>{{ $vehiculo->modelo }}</td>
                                <td>{{ $vehiculo->anio ?? '—' }}</td>
                                <td>{{ $vehiculo->color ?? '—' }}</td>
                                <td>{{ $vehiculo->kilometraje ? number_format((float) $vehiculo->kilometraje, 0, ',', '.') . ' km' : '—' }}</td>
                                <td>${{ number_format((float) ($vehiculo->precio_venta ?? 0), 0, ',', '.') }}</td>
                                <td><span class="badge {{ $estadoClass }}">{{ ucfirst($vehiculo->estado) }}</span></td>
                                <td>{{ $ubicacionLabels[(string) $vehiculo->ubicacion] ?? ucfirst((string) $vehiculo->ubicacion) ?? '—' }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a class="action-btn view" href="{{ route('vehiculos.show', $vehiculo) }}" aria-label="Ver vehículo"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.8 12S6.2 5.5 12 5.5 21.2 12 21.2 12 17.8 18.5 12 18.5 2.8 12 2.8 12Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="2.8" stroke="currentColor" stroke-width="1.7"/></svg></a>
                                        <a class="action-btn edit" href="{{ route('vehiculos.edit', $vehiculo) }}" aria-label="Editar vehículo"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0 0-3l-.5-.5a2.1 2.1 0 0 0-3 0l-10 10L4 20Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m14.5 6.5 3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></a>
                                        <form action="{{ route('vehiculos.destroy', $vehiculo) }}" method="POST" onsubmit="return confirm('Seguro que deseas eliminar este vehiculo?')">
                                            @csrf
                                            @method('delete')
                                            <button class="action-btn delete" type="submit" aria-label="Eliminar vehículo"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7m-8 0 .8 12.2A1.8 1.8 0 0 0 9.6 21h4.8a1.8 1.8 0 0 0 1.8-1.8L17 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="13" style="padding:20px 16px;color:#94A3B8;">No hay vehículos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <div>Mostrando {{ $vehiculos->firstItem() ?? 0 }} a {{ $vehiculos->lastItem() ?? 0 }} de {{ number_format($vehiculos->total(), 0, ',', '.') }} vehículos</div>
                <div class="pagination">
                    <a href="{{ $vehiculos->previousPageUrl() ?: '#' }}" class="page-btn {{ $vehiculos->onFirstPage() ? 'disabled' : '' }}">Anterior</a>
                    @for ($page = 1; $page <= min(5, $vehiculos->lastPage()); $page++)
                        <a href="{{ $vehiculos->url($page) }}" class="page-btn {{ $vehiculos->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>
                    @endfor
                    @if ($vehiculos->lastPage() > 6)
                        <span class="page-btn disabled">...</span>
                    @endif
                    @if ($vehiculos->lastPage() > 5)
                        <a href="{{ $vehiculos->url($vehiculos->lastPage()) }}" class="page-btn {{ $vehiculos->currentPage() === $vehiculos->lastPage() ? 'active' : '' }}">{{ $vehiculos->lastPage() }}</a>
                    @endif
                    <a href="{{ $vehiculos->nextPageUrl() ?: '#' }}" class="page-btn {{ $vehiculos->hasMorePages() ? '' : 'disabled' }}">Siguiente</a>
                </div>
            </div>
        </section>
    </section>

    @push('styles')
        <style>
            .vehicles-page{padding:24px 34px 34px;background:transparent;color:#F8FAFC}
            .vehicles-header{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:22px}
            .btn-new-vehicle{height:44px;padding:0 22px;border-radius:10px;background:linear-gradient(90deg,#2563EB,#7C3AED);color:#fff;font-size:14px;font-weight:700;display:inline-flex;align-items:center;gap:10px;box-shadow:0 12px 26px rgba(37,99,235,.28);transition:all .2s ease-in-out;text-decoration:none}
            .btn-new-vehicle:hover{transform:translateY(-1px);box-shadow:0 16px 32px rgba(37,99,235,.35)}
            .btn-new-vehicle svg{width:16px;height:16px}
            .vehicle-stats-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin-bottom:18px}
            .vehicle-stat-card{min-height:116px;padding:18px 20px;border-radius:12px;background:linear-gradient(180deg,rgba(30,41,59,.94),rgba(15,23,42,.96));border:1px solid rgba(148,163,184,.16);box-shadow:0 16px 36px rgba(0,0,0,.18);display:flex;flex-direction:column;justify-content:space-between}
            .vehicle-filters{display:grid;grid-template-columns:2fr .8fr .8fr .8fr auto auto;gap:12px;padding:14px 16px;border-radius:12px;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.16);margin-bottom:14px;align-items:center}
            .vehicle-export-selection{display:flex;align-items:center;gap:10px;margin:0 0 14px;color:#CBD5E1;font-size:13px}
            .vehicle-table-card{border-radius:12px;background:linear-gradient(180deg,rgba(30,41,59,.94),rgba(15,23,42,.96));border:1px solid rgba(148,163,184,.16);box-shadow:0 16px 36px rgba(0,0,0,.18);overflow:hidden}
            .vehicle-table{width:100%;border-collapse:collapse;min-width:1360px}
            .vehicle-table thead{background:rgba(15,23,42,.54)}
            .vehicle-table th{padding:14px 16px;text-align:left;color:#E2E8F0;font-size:13px;font-weight:700}
            .vehicle-table td{padding:14px 16px;color:#CBD5E1;font-size:13px;border-top:1px solid rgba(148,163,184,.10);vertical-align:middle}
            .vehicle-info{display:flex;align-items:center;gap:12px}
            .vehicle-thumb,.vehicle-placeholder{width:58px;height:40px;border-radius:8px;object-fit:cover;background:rgba(15,23,42,.80);border:1px solid rgba(148,163,184,.16)}
            .vehicle-placeholder{display:flex;align-items:center;justify-content:center;color:#3B82F6}
            .vehicle-placeholder svg{width:20px;height:20px}
            .vehicle-name{font-size:14px;font-weight:700;color:#F8FAFC}
            .vehicle-subtitle{font-size:12px;color:#94A3B8}
            .badge{display:inline-flex;align-items:center;height:24px;padding:0 10px;border-radius:7px;font-size:12px;font-weight:700}
            .badge-green{color:#4ADE80;background:rgba(34,197,94,.18)}
            .badge-purple{color:#C084FC;background:rgba(124,58,237,.20)}
            .badge-blue{color:#60A5FA;background:rgba(37,99,235,.20)}
            .badge-orange{color:#FDBA74;background:rgba(249,115,22,.20)}
            .badge-yellow{color:#FACC15;background:rgba(250,204,21,.16)}
            .badge-red{color:#F87171;background:rgba(239,68,68,.18)}
            .action-buttons{display:flex;gap:6px}
            .action-btn{width:30px;height:30px;border-radius:7px;background:rgba(15,23,42,.80);border:1px solid rgba(148,163,184,.16);display:inline-flex;align-items:center;justify-content:center;transition:all .2s ease;text-decoration:none}
            .action-btn svg{width:15px;height:15px}
            .action-btn.view{color:#3B82F6}
            .action-btn.edit{color:#F59E0B}
            .action-btn.delete{color:#EF4444}
            .action-btn:hover{transform:translateY(-1px);border-color:rgba(255,255,255,.18)}
            .table-footer{display:flex;justify-content:space-between;align-items:center;padding:14px 4px 0;color:#94A3B8;font-size:13px;gap:12px;flex-wrap:wrap}
            .pagination{display:flex;gap:6px;align-items:center;flex-wrap:wrap}
            .page-btn{height:34px;min-width:34px;padding:0 12px;border-radius:8px;background:rgba(15,23,42,.80);border:1px solid rgba(148,163,184,.16);color:#CBD5E1;display:inline-flex;align-items:center;justify-content:center;text-decoration:none}
            .page-btn.active{background:linear-gradient(90deg,#2563EB,#7C3AED);color:#fff}
            .page-btn.disabled{opacity:.45;pointer-events:none}
            @media (max-width:1280px){.vehicle-stats-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.vehicle-filters{grid-template-columns:1fr 1fr 1fr 1fr auto auto}}
            @media (max-width:1024px){.vehicles-page{padding:20px 16px 28px}.vehicle-stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.vehicle-filters{grid-template-columns:1fr 1fr}.vehicle-table{min-width:1180px}}
            @media (max-width:768px){.vehicles-header{flex-direction:column;align-items:flex-start}.vehicle-stats-grid{grid-template-columns:1fr}.vehicle-filters{grid-template-columns:1fr}.vehicle-table{min-width:1120px}}
        </style>
    @endpush
</x-app-layout>
