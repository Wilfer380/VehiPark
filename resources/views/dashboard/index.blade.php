@php
    $kpis = $kpis ?? [
        ['title' => 'Vehículos disponibles', 'value' => $vehicleStats['available'] ?? 0, 'note' => 'Actualizado ahora', 'iconBg' => 'bg-blue-500', 'iconText' => 'text-white'],
        ['title' => 'Vehículos vendidos', 'value' => 0, 'note' => 'Actualizado ahora', 'iconBg' => 'bg-emerald-500', 'iconText' => 'text-white'],
        ['title' => 'Vehículos parqueados', 'value' => 0, 'note' => 'Actualizado ahora', 'iconBg' => 'bg-violet-500', 'iconText' => 'text-white'],
        ['title' => 'Cupos libres', 'value' => 0, 'note' => 'Actualizado ahora', 'iconBg' => 'bg-orange-500', 'iconText' => 'text-white'],
        ['title' => 'Ingresos del día', 'value' => '$0', 'note' => 'Actualizado ahora', 'iconBg' => 'bg-teal-500', 'iconText' => 'text-white'],
    ];

    $movimientos = $movimientos ?? [];

    $ventas = $ventas ?? [];

    $alerts = $alerts ?? [];

    $summary = $summary ?? [
        ['label' => 'Publicaciones activas', 'value' => $vehicleStats['total'] ?? 75, 'icon' => 'car'],
        ['label' => 'Disponibles', 'value' => $vehicleStats['available'] ?? 48, 'icon' => 'users'],
        ['label' => 'Segmentos', 'value' => $vehicleStats['segments'] ?? 12, 'icon' => 'clock'],
        ['label' => 'Valor inventario', 'value' => '$' . number_format((float) ($vehicleStats['inventoryValue'] ?? 0), 0, ',', '.'), 'icon' => 'dollar'],
    ];

    $dashboardMood = $catalogMood ?? [
        'eyebrow' => 'VehiPark Control',
        'title' => 'Panel de administración de flota',
        'subtitle' => 'Gestiona ventas, parqueadero, cupos, tarifas y pagos desde una experiencia interna.',
        'chips' => ['Disponibilidad', 'Asignación', 'Mantenimiento', 'Administración'],
        'spotlightProducts' => [],
    ];

    $salesChartData = $salesChart ?? array_fill(0, 31, 0);
@endphp

@push('styles')
    <style>
        :root{--dash-bg:#0b1220;--dash-surface:#111827;--dash-panel:#1e293b;--dash-border:rgba(148,163,184,.16);--dash-text:#f8fafc;--dash-muted:#94a3b8;--dash-blue:#3b82f6;--dash-violet:#7c3aed;--dash-green:#22c55e;--dash-red:#ef4444;--dash-orange:#f97316;--dash-amber:#f59e0b}
        [x-cloak]{display:none !important}
        .dashboard-shell{min-height:100vh;display:block;background:radial-gradient(circle at top left,rgba(59,130,246,.12),transparent 35%),radial-gradient(circle at top right,rgba(124,58,237,.12),transparent 35%),var(--dash-bg);color:var(--dash-text);font-family:'Inter','Segoe UI',sans-serif;}
        .sidebar{position:fixed;inset:0 auto 0 0;width:300px;background:linear-gradient(180deg,#08111f 0%,#0b1628 100%);border-right:1px solid rgba(148,163,184,.15);padding:22px 20px;z-index:50;transition:transform .2s ease;}
        .sidebar-inner{display:flex;flex-direction:column;height:100%;gap:18px}
        .sidebar-brand{display:flex;align-items:center;gap:14px;position:relative;padding:6px 4px 12px}
        .sidebar-brand__mark{width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,rgba(37,99,235,.95),rgba(124,58,237,.95));display:grid;place-items:center;box-shadow:0 12px 24px rgba(37,99,235,.22)}
        .sidebar-brand__vp{font-weight:900;font-size:1rem;letter-spacing:-.05em}
        .logo-text{font-size:28px;font-weight:800;letter-spacing:-.5px;line-height:1;color:#f8fafc}
        .logo-text span{color:#3b82f6}
        .sidebar-collapse{margin-left:auto;width:38px;height:38px;border-radius:12px;border:1px solid rgba(148,163,184,.15);background:rgba(15,23,42,.72);color:#e2e8f0;display:grid;place-items:center}
        .sidebar-collapse svg{width:20px;height:20px}
        .sidebar-nav{display:flex;flex-direction:column;gap:8px;overflow:auto;padding-right:2px;flex:1}
        .sidebar-link{display:flex;align-items:center;gap:14px;height:48px;padding:0 16px;border-radius:10px;color:#cbd5e1;font-size:15px;font-weight:500;transition:all .2s ease;text-decoration:none}
        .sidebar-link:hover{background:rgba(255,255,255,.06);color:#fff}
        .sidebar-link.active{background:linear-gradient(90deg,#2563eb,#4f46e5);color:#fff;box-shadow:0 10px 24px rgba(37,99,235,.28)}
        .sidebar-link__icon{width:22px;height:22px;display:grid;place-items:center;flex:none}
        .sidebar-link__icon svg{width:22px;height:22px;color:currentColor}
        .sidebar-footer{margin-top:auto;border-top:1px solid rgba(148,163,184,.12);padding-top:18px;display:grid;gap:14px}
        .sidebar-footer__car{width:100%;max-width:220px;margin:0 auto;display:block;filter:drop-shadow(0 18px 18px rgba(0,0,0,.35));opacity:.92;border-radius:18px}
        .sidebar-footer__meta{display:grid;gap:6px;color:#cbd5e1;font-size:.95rem}
        .sidebar-footer__meta strong{font-size:1rem;color:#fff}
        .sidebar-footer__meta span{color:#94a3b8}
        .sidebar-footer__meta p{display:flex;align-items:center;gap:8px;color:#cbd5e1;margin:0}
        .sidebar-status-dot{width:10px;height:10px;border-radius:999px;background:#22c55e;box-shadow:0 0 0 4px rgba(34,197,94,.12);display:inline-block}
        .sidebar-overlay{position:fixed;inset:0;background:rgba(2,6,23,.72);z-index:40}
        .main-content{margin-left:300px;width:calc(100% - 300px);min-height:100vh;display:flex;flex-direction:column}
        .main-content.is-collapsed{margin-left:0;width:100%}
        .topbar{height:74px;border-bottom:1px solid rgba(148,163,184,.14);display:flex;align-items:center;justify-content:space-between;padding:0 28px 0 34px;background:rgba(15,23,42,.56);backdrop-filter:blur(16px);position:sticky;top:0;z-index:20}
        .topbar-left{display:flex;align-items:center;gap:18px;min-width:0;flex:1}
        .topbar-toggle{width:42px;height:42px;border-radius:12px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.72);color:#e2e8f0;display:grid;place-items:center;flex:none}
        .topbar-toggle svg{width:20px;height:20px}
        .search-box-wrap{position:relative;max-width:520px;flex:1}
        .search-box{width:100%;height:42px;background:rgba(15,23,42,.78);border:1px solid rgba(148,163,184,.18);border-radius:10px;color:#e2e8f0;padding:0 44px 0 16px;outline:none}
        .search-box::placeholder{color:#94a3b8}
        .search-box-icon{position:absolute;right:14px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:#94a3b8;pointer-events:none}
        .topbar-right{display:flex;align-items:center;gap:18px;flex:none}
        .topbar-bell{position:relative;width:44px;height:44px;border-radius:14px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.72);color:#e2e8f0;display:grid;place-items:center}
        .topbar-bell svg{width:21px;height:21px}
        .topbar-bell__badge{position:absolute;top:-5px;right:-5px;width:22px;height:22px;border-radius:999px;background:#7c3aed;color:#fff;font-size:12px;font-weight:800;display:grid;place-items:center;border:2px solid #0b1220}
        .topbar-user-wrap{position:relative}
        .topbar-user{display:flex;align-items:center;gap:12px;padding-left:18px;border-left:1px solid rgba(148,163,184,.18);text-align:left}
        .topbar-user__avatar{width:42px;height:42px;border-radius:999px;background:linear-gradient(135deg,#7c3aed,#3b82f6);display:grid;place-items:center;font-weight:800;color:#fff;box-shadow:0 12px 24px rgba(124,58,237,.18)}
        .topbar-user__meta{display:grid;line-height:1.15}
        .topbar-user__meta strong{font-size:14px;color:#f8fafc}
        .topbar-user__meta span{font-size:13px;color:#94a3b8}
        .topbar-user__chevron{width:18px;height:18px;color:#94a3b8}
        .topbar-user-menu{position:absolute;top:calc(100% + 12px);right:0;width:min(320px,calc(100vw - 24px));border-radius:18px;background:linear-gradient(180deg,rgba(248,250,252,.96),rgba(226,232,240,.9));border:1px solid rgba(124,58,237,.18);box-shadow:0 26px 52px rgba(15,23,42,.28);padding:14px;z-index:30;color:#10213f}
        .topbar-user-menu__head{display:flex;align-items:center;gap:12px;padding:4px 2px 12px;border-bottom:1px solid rgba(148,163,184,.22)}
        .topbar-user-menu__avatar{width:44px;height:44px;border-radius:999px;background:linear-gradient(135deg,#7c3aed,#3b82f6);display:grid;place-items:center;font-weight:800;color:#fff;box-shadow:0 12px 24px rgba(124,58,237,.16)}
        .topbar-user-menu__head strong{display:block;font-size:15px;font-weight:800;color:#10213f}
        .topbar-user-menu__head span{display:block;font-size:13px;color:#64748b;word-break:break-all}
        .topbar-user-menu__section{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 2px}
        .topbar-user-menu__section-label{font-size:12px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.12em}
        .topbar-user-menu__pill{padding:6px 10px;border-radius:999px;background:rgba(124,58,237,.10);color:#6d28d9;font-size:12px;font-weight:800}
        .topbar-user-menu__action{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;padding:12px 14px;border-radius:14px;border:1px solid rgba(148,163,184,.18);background:rgba(255,255,255,.72);color:#10213f;text-decoration:none;font-weight:700}
        .topbar-user-menu__action:hover{border-color:rgba(124,58,237,.28);background:rgba(255,255,255,.92)}
        .topbar-user-menu__action--danger{color:#b91c1c}
        .topbar-user-menu__logout{margin-top:10px}
        .dashboard-content{padding:26px 34px 36px 34px}
        .page-title{font-size:30px;font-weight:800;color:#f8fafc;margin-bottom:4px}
        .page-subtitle{font-size:15px;color:#94a3b8}
        .hero-panel{margin-top:12px;border-radius:16px;background:linear-gradient(135deg,rgba(37,99,235,.16),rgba(124,58,237,.12)),linear-gradient(180deg,rgba(30,41,59,.94),rgba(15,23,42,.96));border:1px solid rgba(148,163,184,.16);box-shadow:0 16px 36px rgba(0,0,0,.18);padding:20px}
        .hero-panel__eyebrow{color:#60a5fa;font-size:12px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
        .hero-panel__title{font-size:26px;font-weight:800;line-height:1.15;color:#f8fafc;margin-top:8px}
        .hero-panel__subtitle{margin-top:8px;color:#cbd5e1;font-size:15px;max-width:72ch}
        .hero-chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
        .hero-chip{padding:7px 12px;border-radius:999px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.55);color:#e2e8f0;font-size:13px}
        .hero-spotlight{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:18px}
        .spotlight-card{padding:14px;border-radius:14px;background:rgba(15,23,42,.58);border:1px solid rgba(148,163,184,.12)}
        .spotlight-card span{display:block;color:#94a3b8;font-size:12px;margin-bottom:6px}
        .spotlight-card strong{display:block;color:#fff;font-size:15px;line-height:1.3}
        .kpi-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin-top:18px}
        .kpi-card{min-height:150px;padding:20px;border-radius:12px;background:linear-gradient(180deg,rgba(30,41,59,.94),rgba(15,23,42,.94));border:1px solid rgba(148,163,184,.16);box-shadow:0 16px 36px rgba(0,0,0,.18);display:flex;flex-direction:column;justify-content:space-between}
        .kpi-card__top{display:flex;gap:14px;align-items:flex-start}
        .kpi-icon{width:52px;height:52px;border-radius:12px;display:grid;place-items:center;flex:none;box-shadow:0 10px 24px rgba(0,0,0,.16)}
        .kpi-icon svg{width:26px;height:26px}
        .kpi-card__copy{min-width:0}
        .kpi-card__copy h3{font-size:15px;font-weight:600;line-height:1.25;color:#f8fafc;margin-bottom:10px}
        .kpi-value{font-size:30px;font-weight:800;color:#fff}
        .kpi-note{font-size:13px;color:#94a3b8}
        .dashboard-grid{display:grid;grid-template-columns:2fr 1.1fr 1.8fr;gap:14px;margin-top:14px}
        .bottom-grid{display:grid;grid-template-columns:2fr 1.5fr 1.4fr;gap:14px;margin-top:14px}
        .panel-card{border-radius:12px;background:linear-gradient(180deg,rgba(30,41,59,.94),rgba(15,23,42,.96));border:1px solid rgba(148,163,184,.16);box-shadow:0 16px 36px rgba(0,0,0,.18);padding:18px 20px}
        .panel-card__head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:14px}
        .panel-card__title{font-size:18px;font-weight:700;color:#f8fafc}
        .panel-card__link{font-size:14px;color:#60a5fa;text-decoration:none}
        .panel-card__muted{color:#94a3b8;font-size:13px}
        .chart-box{position:relative;height:290px}
        .chart-box--doughnut{height:290px;display:grid;place-items:center}
        .chart-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;border-top:1px solid rgba(148,163,184,.12);padding-top:14px;margin-top:14px}
        .chart-stat{display:grid;gap:4px}
        .chart-stat span{color:#94a3b8;font-size:13px}
        .chart-stat strong{font-size:18px;color:#f8fafc}
        .chart-stat strong.green{color:#4ade80}
        .doughnut-center{position:absolute;inset:0;display:grid;place-items:center;text-align:center;pointer-events:none}
        .doughnut-center strong{font-size:32px;font-weight:800;color:#fff;line-height:1}
        .doughnut-center span{font-size:14px;color:#94a3b8}
        .movement-list{display:grid;gap:12px}
        .movement-item{display:grid;grid-template-columns:42px minmax(0,1fr) auto;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(148,163,184,.10)}
        .movement-item:last-child{border-bottom:0}
        .movement-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center}
        .movement-icon svg{width:20px;height:20px}
        .movement-icon.green{background:rgba(34,197,94,.14);color:#4ade80}
        .movement-icon.red{background:rgba(239,68,68,.14);color:#f87171}
        .movement-icon.blue{background:rgba(59,130,246,.14);color:#60a5fa}
        .movement-main{display:flex;align-items:center;gap:14px;min-width:0}
        .movement-main strong{color:#f8fafc;font-weight:700}
        .movement-main span{color:#cbd5e1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .movement-time{color:#cbd5e1;font-size:14px;white-space:nowrap}
        .movements-footer{margin-top:12px;border-top:1px solid rgba(148,163,184,.12);padding-top:12px}
        .movements-button{display:flex;align-items:center;justify-content:center;gap:10px;height:44px;border-radius:10px;border:1px solid rgba(148,163,184,.18);color:#60a5fa;text-decoration:none;background:rgba(15,23,42,.54)}
        .sales-table{width:100%;border-collapse:collapse}
        .sales-table th,.sales-table td{padding:12px 8px;border-bottom:1px solid rgba(148,163,184,.10);font-size:14px}
        .sales-table th{color:#94a3b8;text-align:left;font-weight:600}
        .sales-table td{color:#e2e8f0}
        .sales-table td.value{color:#86efac;font-weight:700}
        .alert-list{display:grid;gap:12px}
        .alert-item{display:grid;grid-template-columns:42px minmax(0,1fr) auto;gap:12px;align-items:center;padding:10px 0;border-bottom:1px solid rgba(148,163,184,.10)}
        .alert-item:last-child{border-bottom:0}
        .alert-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center}
        .alert-icon svg{width:22px;height:22px}
        .alert-icon.amber{background:rgba(245,158,11,.12);color:#f59e0b}
        .alert-icon.blue{background:rgba(59,130,246,.12);color:#60a5fa}
        .alert-icon.red{background:rgba(239,68,68,.12);color:#f87171}
        .alert-item strong{display:block;color:#f8fafc;font-weight:700;margin-bottom:4px}
        .alert-item p{margin:0;color:#cbd5e1;font-size:14px}
        .alert-chevron{color:#64748b}
        .summary-list{display:grid;gap:10px}
        .summary-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;border-radius:10px;background:rgba(15,23,42,.56);border:1px solid rgba(148,163,184,.10)}
        .summary-item__left{display:flex;align-items:center;gap:12px;min-width:0}
        .summary-item__icon{width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.04);display:grid;place-items:center;color:#cbd5e1}
        .summary-item__icon svg{width:18px;height:18px}
        .summary-item__label{color:#cbd5e1;font-size:14px}
        .summary-item__value{color:#fff;font-weight:800;white-space:nowrap}
        .section-head{display:flex;align-items:baseline;justify-content:space-between;gap:14px;margin-bottom:12px}
        .section-head h3{font-size:18px;font-weight:700;color:#f8fafc}
        .section-head a{color:#60a5fa;text-decoration:none;font-size:14px}
        .panel-subtext{font-size:13px;color:#94a3b8}
        @media (max-width: 1280px){
            .kpi-grid{grid-template-columns:repeat(3,minmax(0,1fr))}
            .dashboard-grid,.bottom-grid{grid-template-columns:1fr}
            .hero-spotlight{grid-template-columns:repeat(2,minmax(0,1fr))}
        }
        @media (max-width: 1024px){
            .main-content{margin-left:0;width:100%}
            .topbar{padding:0 16px}
            .search-box-wrap{max-width:none}
            .topbar-user__meta{display:none}
        }
        @media (max-width: 768px){
            .dashboard-content{padding:20px 16px 28px}
            .kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
            .topbar{height:auto;min-height:74px;flex-wrap:wrap;gap:12px;padding:12px 16px}
            .topbar-left{width:100%}
            .topbar-right{margin-left:auto}
            .search-box-wrap{width:100%}
            .hero-spotlight{grid-template-columns:1fr}
            .topbar-user-menu{right:auto;left:0;width:min(320px,calc(100vw - 32px))}
        }
        @media (max-width: 640px){
            .kpi-grid{grid-template-columns:1fr}
            .sales-table{min-width:720px}
            .panel-card{padding:16px}
        }
    </style>
@endpush

<section class="dashboard-content">
    <div class="mb-2">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Resumen general del negocio</p>
    </div>

    <section class="hero-panel">
        <div class="hero-panel__eyebrow">{{ $dashboardMood['eyebrow'] }}</div>
        <h2 class="hero-panel__title">{{ $dashboardMood['title'] }}</h2>
        <p class="hero-panel__subtitle">{{ $dashboardMood['subtitle'] }}</p>

        <div class="hero-chips">
            @foreach ($dashboardMood['chips'] as $chip)
                <span class="hero-chip">{{ $chip }}</span>
            @endforeach
        </div>

        @if (! empty($dashboardMood['spotlightProducts']))
            <div class="hero-spotlight">
                @foreach ($dashboardMood['spotlightProducts'] as $spotlight)
                    <div class="spotlight-card">
                        <span>{{ $spotlight['stock'] }} disponibles</span>
                        <strong>{{ $spotlight['name'] }}</strong>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <div class="kpi-grid">
        @foreach ($kpis as $kpi)
            <x-kpi-card :title="$kpi['title']" :value="$kpi['value']" :note="$kpi['note']" :icon-bg="$kpi['iconBg']" :icon-text="$kpi['iconText']">
                @if ($loop->index === 0)
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 14v4m0-4h14l-1.4-4.3A2 2 0 0 0 15.7 8H8.3a2 2 0 0 0-1.9 1.7L5 14Zm3.2 4.2a1.8 1.8 0 1 0 0 .1Zm10 0a1.8 1.8 0 1 0 0 .1Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                @elseif ($loop->index === 1)
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 5h2l2.4 10.2A2 2 0 0 0 9.4 17H18a2 2 0 0 0 1.9-1.4L22 8H6.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="20" r="1.4" fill="currentColor"/><circle cx="17" cy="20" r="1.4" fill="currentColor"/></svg>
                @elseif ($loop->index === 2)
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3l7 4v5c0 5-3.5 8.5-7 9.8C8.5 20.5 5 17 5 12V7l7-4Z" stroke="currentColor" stroke-width="1.7"/><path d="M10.2 8.9h3.6a1.7 1.7 0 0 1 0 3.4h-2v2.8M12 8.9v6.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                @elseif ($loop->index === 3)
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4v16M4 12h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M8.5 8.5 12 5l3.5 3.5M8.5 15.5 12 19l3.5-3.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v18M5 8h9a4 4 0 0 1 0 8H9a4 4 0 0 1 0-8h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @endif
            </x-kpi-card>
        @endforeach
    </div>

    <div class="dashboard-grid">
        <section class="panel-card">
            <div class="panel-card__head">
                <div>
                    <div class="panel-card__title">Ventas del mes</div>
                </div>
                <div class="panel-card__muted">Mayo 2024</div>
            </div>
            <div class="chart-box">
                <canvas id="salesChart"></canvas>
            </div>
            <div class="chart-stats">
                <div class="chart-stat"><span>Total del mes</span><strong>${{ number_format((float) ($salesMonthTotal ?? 0), 0, ',', '.') }}</strong></div>
                <div class="chart-stat"><span>Promedio diario</span><strong>${{ number_format((float) ($salesAverageDaily ?? 0), 0, ',', '.') }}</strong></div>
                <div class="chart-stat"><span>Mejor día</span><strong class="green">${{ number_format((float) ($bestDay ?? 0), 0, ',', '.') }}</strong></div>
            </div>
        </section>

        <section class="panel-card">
            <div class="panel-card__head">
                <div class="panel-card__title">Ocupación del parqueadero</div>
            </div>
            <div class="chart-box chart-box--doughnut">
                <canvas id="occupancyChart"></canvas>
                <div class="doughnut-center"><strong>{{ ($parkingStats['total'] ?? 0) > 0 ? round((($parkingStats['occupied'] ?? 0) / $parkingStats['total']) * 100) : 0 }}%</strong><span>Ocupado</span></div>
            </div>
            <div class="chart-stats" style="grid-template-columns:1fr 1fr 1fr">
                <div class="chart-stat"><span>Ocupados</span><strong>{{ $parkingStats['occupied'] ?? 0 }}</strong></div>
                <div class="chart-stat"><span>Libres</span><strong>{{ $parkingStats['available'] ?? 0 }}</strong></div>
                <div class="chart-stat"><span>Total de cupos</span><strong>{{ $parkingStats['total'] ?? 0 }}</strong></div>
            </div>
        </section>

        <section class="panel-card">
            <div class="section-head">
                <h3>Últimos movimientos</h3>
                <a href="#">Ver todos</a>
            </div>
            <div class="movement-list">
                @forelse ($movimientos as $movement)
                    <div class="movement-item">
                        <div class="movement-icon {{ $movement['tone'] }}">
                            @if ($movement['icon'] === 'car')
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 14h14l-1.2-4.2A2 2 0 0 0 16.9 8H7.1a2 2 0 0 0-1.9 1.8L5 14Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="8" cy="17" r="1.4" stroke="currentColor" stroke-width="1.7"/><circle cx="16" cy="17" r="1.4" stroke="currentColor" stroke-width="1.7"/></svg>
                            @elseif ($movement['icon'] === 'arrow-out')
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 12h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="m12 7 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M18 12H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="m12 7-5 5 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @endif
                        </div>
                        <div class="movement-main">
                            <strong>{{ $movement['plate'] }}</strong>
                            <span>{{ $movement['type'] }}</span>
                        </div>
                        <div class="movement-time">{{ $movement['time'] }}</div>
                    </div>
                @empty
                    <div class="panel-card__muted">No hay movimientos registrados aún.</div>
                @endforelse
            </div>
            <div class="movements-footer">
                <a class="movements-button" href="#">Ver todos los movimientos <span>›</span></a>
            </div>
        </section>
    </div>

    <div class="bottom-grid">
        <section class="panel-card">
            <div class="section-head">
                <h3>Últimas ventas</h3>
                <a href="#">Ver todas</a>
            </div>
            <div class="overflow-x-auto">
                <table class="sales-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Placa</th>
                            <th>Vehículo</th>
                            <th>Cliente</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ventas as $sale)
                            <tr>
                                <td>{{ $sale['date'] }}</td>
                                <td>{{ $sale['plate'] }}</td>
                                <td>{{ $sale['vehicle'] }}</td>
                                <td>{{ $sale['client'] }}</td>
                                <td class="value">{{ $sale['value'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="panel-card__muted">No hay ventas registradas aún.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel-card">
            <div class="section-head">
                <h3>Alertas</h3>
                <a href="#">Ver todas</a>
            </div>
            <div class="alert-list">
                @forelse ($alerts as $alert)
                    <div class="alert-item">
                        <div class="alert-icon {{ $alert['tone'] }}">
                            @if ($alert['icon'] === 'calendar')
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 3v3M17 3v3M4 8h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                            @elseif ($alert['icon'] === 'alert')
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 2.8 19h18.4L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 9v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="16.8" r="1" fill="currentColor"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 2.8 19h18.4L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 9v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="16.8" r="1" fill="currentColor"/></svg>
                            @endif
                        </div>
                        <div>
                            <strong>{{ $alert['title'] }}</strong>
                            <p>{{ $alert['desc'] }}</p>
                        </div>
                        <div class="alert-chevron">›</div>
                    </div>
                @empty
                    <div class="panel-card__muted">Sin alertas activas.</div>
                @endforelse
            </div>
        </section>

        <section class="panel-card">
            <div class="section-head">
                <h3>Resumen rápido</h3>
            </div>
            <div class="summary-list">
                @forelse ($summary as $item)
                    <div class="summary-item">
                        <div class="summary-item__left">
                            <div class="summary-item__icon">
                                @switch($item['icon'])
                                    @case('users')
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M16 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="9.5" cy="7.5" r="3.5" stroke="currentColor" stroke-width="1.7"/></svg>
                                        @break
                                    @case('car')
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 14h14l-1.2-4.2A2 2 0 0 0 16.9 8H7.1a2 2 0 0 0-1.9 1.8L5 14Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="8" cy="17" r="1.4" stroke="currentColor" stroke-width="1.7"/><circle cx="16" cy="17" r="1.4" stroke="currentColor" stroke-width="1.7"/></svg>
                                        @break
                                    @case('clock')
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.7"/><path d="M12 7.5V12l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @break
                                    @default
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4v16M5 8h9a4 4 0 0 1 0 8H9a4 4 0 0 1 0-8h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                @endswitch
                            </div>
                            <div class="summary-item__label">{{ $item['label'] }}</div>
                        </div>
                        <div class="summary-item__value">{{ $item['value'] }}</div>
                    </div>
                @empty
                    <div class="panel-card__muted">Sin resumen disponible.</div>
                @endforelse
            </div>
        </section>
    </div>
</section>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const salesCtx = document.getElementById('salesChart');
            if (salesCtx) {
                new Chart(salesCtx, {
                    type: 'bar',
                    data: {
                        labels: Array.from({ length: 31 }, (_, i) => String(i + 1)),
                        datasets: [
                            {
                                label: 'Ventas (COP)',
                                data: @json($salesChartData),
                                backgroundColor: 'rgba(124, 58, 237, 0.85)',
                                borderRadius: 6,
                                borderSkipped: false,
                                barPercentage: 0.75,
                                categoryPercentage: 0.82,
                            },
                            {
                                label: 'Promedio diario',
                                type: 'line',
                                data: @json($salesChartData),
                                borderColor: '#60a5fa',
                                backgroundColor: '#60a5fa',
                                tension: 0.38,
                                pointRadius: 2,
                                pointHoverRadius: 4,
                                pointBackgroundColor: '#60a5fa',
                                pointBorderColor: '#60a5fa',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: '#64748b', usePointStyle: true, pointStyle: 'circle' },
                            },
                        },
                        scales: {
                            x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(148,163,184,.16)' } },
                            y: { ticks: { color: '#64748b', callback: (v) => v + 'M' }, grid: { color: 'rgba(148,163,184,.16)' } },
                        },
                    },
                });
            }

            const occupancyCtx = document.getElementById('occupancyChart');
            if (occupancyCtx) {
                new Chart(occupancyCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Ocupados', 'Libres'],
                        datasets: [{
                            data: [{{ $parkingStats['occupied'] ?? 0 }}, {{ $parkingStats['available'] ?? 0 }}],
                            backgroundColor: ['#7c3aed', '#3b82f6'],
                            borderWidth: 0,
                            hoverOffset: 2,
                            cutout: '72%',
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    },
                });
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        .dashboard-shell{background:radial-gradient(circle at 12% 10%,rgba(124,58,237,.10),transparent 24%),radial-gradient(circle at 88% 4%,rgba(37,99,235,.10),transparent 26%),linear-gradient(180deg,#EEF3FF 0%,#F8FBFF 100%) !important;color:#10213F !important}
        .sidebar{background:linear-gradient(180deg,rgba(255,255,255,.92),rgba(248,250,252,.96)) !important;border-right:1px solid #D8DDF2 !important;box-shadow:0 18px 44px rgba(70,80,140,.08) !important}
        .sidebar-collapse,.topbar-toggle,.topbar-bell{background:rgba(255,255,255,.88) !important;border:1px solid #D8DDF2 !important;color:#10213F !important}
        .logo-text,.sidebar-link,.sidebar-footer__meta strong,.sidebar-footer__meta p,.topbar-user__meta strong,.page-title,.page-subtitle,.hero-panel__title,.panel-card__title,.kpi-value,.section-head h3,.summary-item__value,.movement-main strong,.alert-item strong,.sales-table td,.sales-table th,.client-name,.vehicle-name,.vehicle-detail-main h2,.profile-section__head h2,.crud-hero h1,.crud-panel h2{color:#10213F !important}
        .logo-text span,.sidebar-brand__mark,.sidebar-link.active,.btn-new-client,.btn-primary,.btn-register,.page-btn.active,.topbar-bell__badge{background:linear-gradient(90deg,#7C3AED,#2563EB) !important;color:#fff !important}
        .sidebar-link:hover,.action-btn:hover,.page-btn:hover,.btn-secondary:hover,.btn-export:hover,.btn-filters:hover,.topbar-user-menu__action:hover{background:rgba(255,255,255,.98) !important}
        .sidebar-link{color:#53637D !important}
        .sidebar-footer,.topbar,.hero-panel,.kpi-card,.panel-card,.sale-kpi-card,.sale-detail-card,.vehicle-detail-card,.profile-card,.crud-hero,.crud-panel,.summary-item,.spotlight-card,.movement-item,.alert-item,.sales-action-ghost,.sales-table thead,.sales-filter-bar,.vehicle-form-card,.vehicle-preview-card,.vehicle-tip-card,.parking-kpi-card,.parking-map-card,.parking-list-card,.parking-status-card,.modal-card{background:rgba(255,255,255,.88) !important;border-color:#D8DDF2 !important;box-shadow:0 18px 44px rgba(70,80,140,.08) !important}
        .topbar-user-menu{background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(248,250,252,.94)) !important;border:1px solid #D8DDF2 !important;box-shadow:0 26px 52px rgba(70,80,140,.16) !important;color:#10213F !important}
        .hero-panel__subtitle,.panel-subtext,.summary-item__label,.movement-main span,.movement-time,.alert-item p,.panel-card__muted,.kpi-note,.page-subtitle,.stat-trend,.crud-muted,.crud-hero p,.crud-row small,.crud-detail span,.crud-detail strong,.client-subtitle,.topbar-user__meta span,.topbar-user-menu__head span,.topbar-user-menu__section-label,.topbar-user-menu__pill{color:#64748B !important}
        .hero-chip,.spotlight-card span,.sales-action-ghost,.btn-secondary,.btn-export,.btn-filters,.action-btn,.page-btn,.filter-input,.filter-select,.client-form-field input,.client-form-field select,.client-form-field textarea,.sale-form .sale-field input,.sale-form .sale-field select,.sale-form .sale-field textarea,.vehicle-form .vehicle-field input,.vehicle-form .vehicle-field select,.vehicle-form .vehicle-field textarea,.profile-field input,.parking-search,.parking-state-filter,.map-filter,.modal-close{background:rgba(255,255,255,.88) !important;border-color:#D8DDF2 !important;color:#10213F !important}
        .hero-chip,.summary-item,.crud-detail div,.sales-table th,.sales-table td,.vehicle-table th,.vehicle-table td,.clients-table th,.clients-table td{border-color:#E5E7F5 !important}
        .password-rule--unmet{color:#F97316 !important}.password-rule--met{color:#10B981 !important}
        .password-rule--unmet svg,.password-rule--met svg{color:currentColor !important}
    </style>
@endpush
