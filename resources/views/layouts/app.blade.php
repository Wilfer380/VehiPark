<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'VehiPark') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        @if (request()->routeIs('dashboard') || request()->routeIs('clientes.*') || request()->routeIs('vehiculos.*') || request()->routeIs('ventas.*') || request()->routeIs('parqueadero.*') || request()->routeIs('tarifas.*') || request()->routeIs('pagos.*') || request()->routeIs('reportes.*') || request()->routeIs('configuracion.*') || request()->routeIs('cupos.*') || request()->routeIs('profile.*'))
            <div x-data="{ sidebarOpen: window.innerWidth >= 1024 }" class="dashboard-shell">
                <x-sidebar />

                <div class="main-content" x-bind:class="sidebarOpen ? '' : 'is-collapsed'">
                    <x-topbar />

                    <main>
                        {{ $slot }}
                    </main>
                </div>
            </div>
        @else
            <div class="min-h-screen bg-slate-950 text-slate-100 lg:flex">
                @include('layouts.navigation')

                <div class="min-w-0 flex-1 lg:pl-72">
                    @isset($header)
                        <header class="border-b border-white/10 bg-slate-900/80 shadow-2xl shadow-black/30">
                            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <main>
                        {{ $slot }}
                    </main>
                </div>
            </div>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const moneyInputs = document.querySelectorAll('[data-money-input="true"]');
                const formatMoney = (input) => {
                    const raw = (input.value || '').trim();
                    if (raw === '') return;
                    if (!/^(?:0|[1-9]\d*|[1-9]\d{0,2}(?:\.\d{3})*)$/.test(raw)) return;

                    const digits = raw.replace(/\./g, '');
                    input.value = new Intl.NumberFormat('es-CO').format(Number(digits));
                };

                moneyInputs.forEach((input) => {
                    formatMoney(input);
                    input.addEventListener('input', () => formatMoney(input));
                    input.addEventListener('blur', () => formatMoney(input));
                });
            });
        </script>

        @stack('scripts')

        <style>
            body,
            .dashboard-shell,
            .main-content,
            .clients-page,
            .vehicles-page,
            .sales-page,
            .sale-detail-page,
            .sale-form-page,
            .parking-page,
            .profile-page,
            .crud-page{color:#10213F !important;background:#EEF3FF !important}
            .page-title,
            .section-head h3,
            .panel-card__title,
            .panel-title,
            .crud-hero h1,
            .crud-panel h2,
            .client-name,
            .vehicle-name,
            .sale-summary-card h2,
            .sale-payments-card h2,
            .vehicle-detail-main h2,
            .profile-section__head h2{color:#10213F !important}
            .sales-table th,
            .vehicle-table th,
            .clients-table th,
            .current-parking-table th,
            .modal-head h3,
            .invoice-box h3,
            .sale-money-grid strong,
            .sale-specs strong,
            .vehicle-specs strong,
            .parking-kpi-value,
            .status-center strong,
            .status-legend-item strong,
            .row-main strong,
            .value-active,
            .sidebar-help-card__copy strong,
            .profile-avatar-copy label,
            .vehicle-form .vehicle-field label span,
            .profile-field label,
            .field label,
            .field span{color:#10213F !important}
            .page-subtitle,
            .panel-subtext,
            .crud-muted,
            .stat-trend,
            .client-subtitle,
            .table-footer,
            .kpi-note,
            .sale-kpi-card__top span,
            .sale-kpi-card small,
            .sale-export-selection,
            .vehicle-export-selection,
            .client-export-selection,
            .hero-panel__subtitle,
            .summary-item__label,
            .movement-main span,
            .movement-time,
            .alert-item p,
            .panel-card__muted,
            .chart-stat span,
            .chart-stat strong.green,
            .doughnut-center span,
            .sale-summary-card p,
            .sale-money-grid span,
            .sale-specs span,
            .sale-kpi-card small,
            .sale-field span,
            .vehicle-preview-card p,
            .vehicle-tip-card li,
            .profile-section__head p,
            .crud-hero p,
            .crud-row small,
            .crud-detail span,
            .crud-detail strong,
            .parking-kpi-label,
            .parking-kpi-subtitle,
            .parking-legend,
            .parking-map-note,
            .zone-title,
            .status-center span,
            .status-legend-item,
            .recent-entry-row,
            .exit-row,
            .row-time,
            .row-slot,
            .modal-head p,
            .sidebar-help-card__copy,
            .sidebar-help-card__copy span,
            .sidebar-footer__meta span,
            .sidebar-footer__meta p,
            .vehicle-subtitle{color:#64748B !important}
            .sales-table td,
            .vehicle-table td,
            .clients-table td,
            .current-parking-table td,
            .parking-kpi-label,
            .parking-kpi-subtitle,
            .parking-legend,
            .zone-title,
            .parking-map-note,
            .status-center span,
            .status-legend-item,
            .recent-entry-row,
            .exit-row,
            .row-main span,
            .row-time,
            .row-slot,
            .modal-head p,
            .field label,
            .field span,
            .sidebar-help-card__copy,
            .sidebar-help-card__copy span,
            .sidebar-footer__meta span,
            .sidebar-footer__meta p,
            .vehicle-subtitle,
            .client-export-selection,
            .vehicle-export-selection,
            .sale-export-selection,
            .profile-avatar-copy input[type="file"],
            .profile-avatar-copy small{color:#64748B !important}
            .hero-panel,
            .kpi-card,
            .panel-card,
            .sale-kpi-card,
            .sale-detail-card,
            .sale-form-card,
            .sale-side-card,
            .vehicle-detail-card,
            .vehicle-form-card,
            .vehicle-preview-card,
            .vehicle-tip-card,
            .profile-card,
            .crud-hero,
            .crud-panel,
            .summary-item,
            .spotlight-card,
            .movement-item,
            .alert-item,
            .sales-action-ghost,
            .sales-table thead,
            .sales-filter-bar,
            .vehicle-stat-card,
            .vehicle-table-card,
            .vehicle-filters,
            .parking-kpi-card,
            .parking-map-card,
            .parking-list-card,
            .parking-status-card,
            .modal-card,
            .client-stat-card,
            .clients-table-card,
            .client-modal,
            .client-filters,
            .client-form-field input,
            .client-form-field select,
            .client-form-field textarea,
            .filter-input,
            .filter-select,
            .btn-export,
            .btn-filters,
            .btn-secondary,
            .action-btn,
            .page-btn,
            .topbar-user-menu__action,
            .search-box,
            .global-search,
            .topbar,
            .topbar-toggle,
            .topbar-bell,
            .sidebar,
            .sidebar-collapse,
            .sidebar-link,
            .sidebar-footer__meta,
            .topbar-user-menu,
            .sale-money-grid div,
            .sale-specs div,
            .vehicle-specs div,
            .vehicle-thumb,
            .vehicle-placeholder,
            .vehicle-preview-image,
            .profile-avatar-upload,
            .parking-slot,
            .parking-slot__vehicle,
            .current-parking-card,
            .current-parking-table thead,
            .icon-btn,
            .modal-head,
            .modal-body,
            .modal-secondary,
            .sidebar-help-card,
            .invoice-box,
            .client-photo-preview{background:rgba(255,255,255,.88) !important;border-color:#D8DDF2 !important;box-shadow:0 18px 44px rgba(70,80,140,.08) !important}
            .hero-chip,
            .btn-new-client,
            .btn-primary,
            .btn-register,
            .btn-new-sale,
            .btn-new-vehicle,
            .crud-button,
            .crud-link,
            .page-btn.active,
            .topbar-bell__badge,
            .sidebar-link.active{background:linear-gradient(90deg,#7C3AED,#2563EB) !important;color:#fff !important;border-color:transparent !important}
            .sidebar-link:hover,
            .action-btn:hover,
            .page-btn:hover,
            .btn-secondary:hover,
            .btn-export:hover,
            .btn-filters:hover,
            .topbar-user-menu__action:hover{background:rgba(255,255,255,.98) !important}
            .filter-input,
            .filter-select,
            .sale-field input,
            .sale-field select,
            .sale-field textarea,
            .vehicle-form .vehicle-field input,
            .vehicle-form .vehicle-field select,
            .vehicle-form .vehicle-field textarea,
            .profile-field input,
            .parking-search,
            .parking-state-filter,
            .map-filter,
            .field input,
            .field select,
            .field textarea,
            .modal-close,
            .modal-secondary,
            .icon-btn{color:#10213F !important;color-scheme:light !important}
            .sale-kpi-card__top span,
            .sale-kpi-card small,
            .sale-export-selection,
            .vehicle-export-selection,
            .client-export-selection,
            .parking-legend,
            .parking-map-note,
            .parking-kpi-label,
            .parking-kpi-subtitle,
            .chart-stat span,
            .doughnut-center span{color:#64748B !important}
            .chart-stat strong.green,
            .doughnut-center strong{color:#047857 !important}
            .sales-table thead,
            .vehicle-table thead,
            .clients-table thead,
            .current-parking-table thead{background:#F8FAFF !important}
            .sales-table th,
            .sales-table td,
            .vehicle-table th,
            .vehicle-table td,
            .clients-table th,
            .clients-table td,
            .current-parking-table th,
            .current-parking-table td,
            .recent-entry-row,
            .exit-row,
            .modal-head{border-color:#E5E7F5 !important}
            .client-modal-backdrop,
            .modal-backdrop{background:rgba(16,33,63,.35) !important;backdrop-filter:blur(12px)}
            .client-modal,
            .modal-card{color:#10213F !important;background:rgba(255,255,255,.94) !important}
            .field input,
            .field select,
            .field textarea,
            .sale-field input,
            .sale-field select,
            .sale-field textarea,
            .vehicle-form .vehicle-field input,
            .vehicle-form .vehicle-field select,
            .vehicle-form .vehicle-field textarea,
            .profile-field input{background:rgba(255,255,255,.92) !important;border-color:#D8DDF2 !important;color:#10213F !important;color-scheme:light !important}
            .vehicle-form .vehicle-upload input::file-selector-button,
            .vehicle-form .vehicle-upload input::-webkit-file-upload-button,
            .profile-avatar-copy input[type="file"]::file-selector-button{background:rgba(124,58,237,.12) !important;color:#2563EB !important}
            .btn-secondary,
            .vehicle-form .crud-link,
            .profile-link,
            .sales-action-ghost,
            .modal-secondary,
            .btn-secondary-parking{background:rgba(255,255,255,.88) !important;border:1px solid #D8DDF2 !important;color:#10213F !important}
            .password-rule--unmet{color:#F97316 !important}
            .password-rule--met{color:#10B981 !important}
            .password-rule--unmet svg,
            .password-rule--met svg{color:currentColor !important}
        </style>
    </body>
</html>
