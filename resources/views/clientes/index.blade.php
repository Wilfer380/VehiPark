<x-app-layout>
    <x-clientes-styles />

    @php
        $avatarGradients = [
            'linear-gradient(135deg,#2563EB,#60A5FA)',
            'linear-gradient(135deg,#15803D,#4ADE80)',
            'linear-gradient(135deg,#6D28D9,#C084FC)',
            'linear-gradient(135deg,#F97316,#F59E0B)',
            'linear-gradient(135deg,#0F766E,#14B8A6)',
            'linear-gradient(135deg,#DC2626,#F87171)',
        ];

        $segmentBadges = [
            'frecuente' => 'badge-purple',
            'activo' => 'badge-blue',
            'nuevo' => 'badge-green',
            'inactivo' => 'badge-red',
        ];

        $statusBadges = [
            'activo' => 'status-active',
            'inactivo' => 'status-inactive',
        ];

        $statusLabel = ['activo' => 'Activo', 'inactivo' => 'Inactivo'];
        $segmentLabel = ['frecuente' => 'Frecuente', 'activo' => 'Activo', 'nuevo' => 'Nuevo', 'inactivo' => 'Inactivo'];
        $currentPage = $clientes->currentPage();
        $lastPage = $clientes->lastPage();
        $from = $clientes->firstItem() ?? 0;
        $to = $clientes->lastItem() ?? 0;
        $total = $clientes->total();
    @endphp

    <section class="clients-page" x-data="{
        createOpen: {{ $errors->any() ? 'true' : 'false' }},
        selectedClients: [],
        selectAll: false,
        query: @js(request()->query()),
        exportHref() {
            const params = new URLSearchParams(this.query);
            if (this.selectedClients.length) {
                params.set('ids', this.selectedClients.join(','));
            } else {
                params.delete('ids');
            }
            return `{{ route('clientes.exportar.excel') }}?${params.toString()}`;
        },
        toggleAll(checked, ids) {
            this.selectAll = checked;
            this.selectedClients = checked ? ids.map(String) : [];
        },
        syncSelectAll(ids) {
            const normalized = ids.map(String);
            this.selectAll = normalized.length > 0 && normalized.every(id => this.selectedClients.includes(id));
        }
    }">
        <div class="clients-header">
            <div>
                <h1 class="page-title">Clientes</h1>
                <p class="page-subtitle">Gestiona la información de tus clientes</p>
            </div>

            <a href="{{ route('clientes.create') }}" @click.prevent="createOpen = true" class="btn-new-client">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span>Nuevo cliente</span>
            </a>
        </div>

        <section class="client-stats-grid">
            @foreach ($stats as $stat)
                <article class="client-stat-card">
                    <div class="client-stat-card__top">
                        <div class="stat-icon icon-{{ $stat['tone'] }}">
                            @if ($stat['icon'] === 'users')
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M16 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="9.5" cy="7.5" r="3.5" stroke="currentColor" stroke-width="1.7"/></svg>
                            @elseif ($stat['icon'] === 'user-check')
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M16 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="9.5" cy="7.5" r="3.5" stroke="currentColor" stroke-width="1.7"/><path d="m15 11 1.5 1.5 3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @elseif ($stat['icon'] === 'user-x')
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M16 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="9.5" cy="7.5" r="3.5" stroke="currentColor" stroke-width="1.7"/><path d="m15 11 4 4m0-4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            @elseif ($stat['icon'] === 'star')
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m12 3 2.9 5.9 6.5.9-4.7 4.5 1.1 6.4L12 17.8 6.2 20.7l1.1-6.4L2.6 9.8l6.5-.9L12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 13h4l2-6 3 12 2-6h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 5h16v14H4z" stroke="currentColor" stroke-width="1.4" opacity=".35"/></svg>
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

        <form method="GET" action="{{ route('clientes.index') }}" class="client-filters">
            <div class="filter-input-wrap">
                <input class="filter-input" type="search" name="q" value="{{ $search }}" placeholder="Buscar clientes por nombre, cédula, teléfono o email...">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            </div>

            <select class="filter-select" name="estado" onchange="this.form.submit()">
                <option value="todos" @selected($estado === 'todos')>Estado: Todos</option>
                @foreach ($statusLabel as $key => $label)
                    <option value="{{ $key }}" @selected($estado === $key)>Estado: {{ $label }}</option>
                @endforeach
            </select>

            <select class="filter-select" name="ciudad" onchange="this.form.submit()">
                <option value="todas" @selected($ciudad === 'todas')>Ciudad: Todas</option>
                @foreach ($ciudades as $item)
                    <option value="{{ $item }}" @selected($ciudad === $item)>Ciudad: {{ $item }}</option>
                @endforeach
            </select>

            <select class="filter-select" name="segmento" onchange="this.form.submit()">
                <option value="todos" @selected($segmento === 'todos')>Segmento: Todos</option>
                @foreach ($segmentLabel as $key => $label)
                    <option value="{{ $key }}" @selected($segmento === $key)>Segmento: {{ $label }}</option>
                @endforeach
            </select>

            <a :href="exportHref()" class="btn-export">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4v10m0 0 4-4m-4 4-4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 17v2h14v-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span>Exportar Excel</span>
            </a>
        </form>

        <div class="client-export-selection">
            <input type="checkbox" :checked="selectAll" @change="toggleAll($event.target.checked, @js($clientes->pluck('id')->all()))">
            <span>Selecciona uno o varios clientes para exportar solo esos registros</span>
        </div>

        @if (session('status'))
            <div class="crud-alert">{{ session('status') }}</div>
        @endif

        <section class="clients-table-card">
            <div class="overflow-x-auto">
                <table class="clients-table">
                    <thead>
                        <tr>
                            <th style="width:44px;"><input type="checkbox" :checked="selectAll" @change="toggleAll($event.target.checked, @js($clientes->pluck('id')->all()))"></th>
                            <th>Cliente</th>
                            <th>Documento</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Ciudad</th>
                            <th>Segmento</th>
                            <th>Estado</th>
                            <th>Compras</th>
                            <th>Última compra</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clientes as $cliente)
                            @php
                                $initials = collect(explode(' ', trim($cliente->nombres . ' ' . ($cliente->apellidos ?? ''))))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn ($part) => mb_substr($part, 0, 1))
                                    ->implode('');
                                $avatarBg = $avatarGradients[$loop->index % count($avatarGradients)];
                                $segmentClass = $segmentBadges[$cliente->segmento ?? 'activo'] ?? 'badge-blue';
                                $stateClass = $statusBadges[$cliente->estado ?? 'activo'] ?? 'status-active';
                                $segmentText = $segmentLabel[$cliente->segmento ?? 'activo'] ?? ucfirst((string) $cliente->segmento);
                                $stateText = $statusLabel[$cliente->estado ?? 'activo'] ?? ucfirst((string) $cliente->estado);
                                $lastPurchase = $cliente->ultima_compra ? \Illuminate\Support\Carbon::parse($cliente->ultima_compra)->format('d/m/Y') : '—';
                            @endphp
                            <tr>
                                <td style="width:44px;">
                                    <input type="checkbox" :value="{{ $cliente->id }}" x-model="selectedClients" @change="syncSelectAll(@js($clientes->pluck('id')->all()))" style="width:16px;height:16px;accent-color:#3B82F6;">
                                </td>
                                <td>
                                    <div class="client-info">
                                        @if ($cliente->foto)
                                            <img src="{{ asset('storage/' . $cliente->foto) }}" class="client-avatar" alt="Foto cliente">
                                        @else
                                            <div class="client-avatar" style="background: {{ $avatarBg }};">{{ $initials ?: 'VP' }}</div>
                                        @endif
                                        <div>
                                            <div class="client-name">{{ $cliente->nombres }} {{ $cliente->apellidos }}</div>
                                            <div class="client-subtitle {{ match($cliente->segmento ?? 'activo') { 'frecuente' => 'sub-purple', 'activo' => 'sub-blue', 'nuevo' => 'sub-green', 'inactivo' => 'sub-red', default => 'sub-blue' } }}">Cliente {{ $segmentText }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $cliente->tipo_documento }} {{ $cliente->documento }}</td>
                                <td>{{ $cliente->telefono ?? 'Sin teléfono' }}</td>
                                <td>{{ $cliente->email ?? 'Sin email' }}</td>
                                <td>{{ $cliente->ciudad ?? 'Sin ciudad' }}</td>
                                <td><span class="badge {{ $segmentClass }}">{{ $segmentText }}</span></td>
                                <td><span class="badge {{ $stateClass }}"><span class="status-dot"></span>{{ $stateText }}</span></td>
                                <td>${{ number_format((float) ($cliente->compras_total ?? 0), 0, ',', '.') }}</td>
                                <td>{{ $lastPurchase }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a class="action-btn view" href="{{ route('clientes.show', $cliente) }}" aria-label="Ver cliente">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.8 12S6.2 5.5 12 5.5 21.2 12 21.2 12 17.8 18.5 12 18.5 2.8 12 2.8 12Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="2.8" stroke="currentColor" stroke-width="1.7"/></svg>
                                        </a>
                                        <a class="action-btn edit" href="{{ route('clientes.edit', $cliente) }}" aria-label="Editar cliente">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0 0-3l-.5-.5a2.1 2.1 0 0 0-3 0l-10 10L4 20Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m14.5 6.5 3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                        </a>
                                        <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" onsubmit="return confirm('Seguro que deseas eliminar este cliente?')">
                                            @csrf
                                            @method('delete')
                                            <button class="action-btn delete" type="submit" aria-label="Eliminar cliente">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7m-8 0 .8 12.2A1.8 1.8 0 0 0 9.6 21h4.8a1.8 1.8 0 0 0 1.8-1.8L17 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" style="padding:20px 16px;color:#94A3B8;">No hay clientes registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <div>Mostrando {{ $from }} a {{ $to }} de {{ number_format($total, 0, ',', '.') }} clientes</div>

                <div class="pagination">
                    <a href="{{ $clientes->previousPageUrl() ?: '#' }}" class="page-btn {{ $clientes->onFirstPage() ? 'disabled' : '' }}">Anterior</a>

                    @for ($page = 1; $page <= min(5, $lastPage); $page++)
                        <a href="{{ $clientes->url($page) }}" class="page-btn {{ $currentPage === $page ? 'active' : '' }}">{{ $page }}</a>
                    @endfor

                    @if ($lastPage > 6)
                        <span class="page-btn disabled">...</span>
                    @endif

                    @if ($lastPage > 5)
                        <a href="{{ $clientes->url($lastPage) }}" class="page-btn {{ $currentPage === $lastPage ? 'active' : '' }}">{{ $lastPage }}</a>
                    @endif

                    <a href="{{ $clientes->nextPageUrl() ?: '#' }}" class="page-btn {{ $clientes->hasMorePages() ? '' : 'disabled' }}">Siguiente</a>
                </div>
            </div>
        </section>

        @include('clientes.partials.create-modal')
    </section>
</x-app-layout>
