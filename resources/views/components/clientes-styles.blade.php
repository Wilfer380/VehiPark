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
    .topbar-user__avatar{width:42px;height:42px;border-radius:999px;background:linear-gradient(135deg,#7c3aed,#3b82f6);display:grid;place-items:center;font-weight:800;color:#fff;box-shadow:0 12px 24px rgba(124,58,237,.18);overflow:hidden;flex:none}
    .topbar-user__avatar img{width:100%;height:100%;object-fit:cover;display:block}
    .topbar-user__meta{display:grid;line-height:1.15}
    .topbar-user__meta strong{font-size:14px;color:#f8fafc}
    .topbar-user__meta span{font-size:13px;color:#94a3b8}
    .topbar-user__chevron{width:18px;height:18px;color:#94a3b8}
    .topbar-user-menu{position:absolute;top:calc(100% + 12px);right:0;width:min(320px,calc(100vw - 24px));border-radius:18px;background:linear-gradient(180deg,rgba(248,250,252,.96),rgba(226,232,240,.9));border:1px solid rgba(124,58,237,.18);box-shadow:0 26px 52px rgba(15,23,42,.28);padding:14px;z-index:30;color:#10213f}
    .topbar-user-menu__head{display:flex;align-items:center;gap:12px;padding:4px 2px 12px;border-bottom:1px solid rgba(148,163,184,.22)}
    .topbar-user-menu__avatar{width:44px;height:44px;border-radius:999px;background:linear-gradient(135deg,#7c3aed,#3b82f6);display:grid;place-items:center;font-weight:800;color:#fff;box-shadow:0 12px 24px rgba(124,58,237,.16);overflow:hidden;flex:none}
    .topbar-user-menu__avatar img{width:100%;height:100%;object-fit:cover;display:block}
    .topbar-user__avatar--circle{border-radius:9999px}
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
    @media (max-width: 1024px){
        .main-content{margin-left:0;width:100%}
        .topbar{padding:0 16px}
        .search-box-wrap{max-width:none}
        .topbar-user__meta{display:none}
    }
    @media (max-width: 768px){
        .dashboard-content{padding:20px 16px 28px}
        .topbar{height:auto;min-height:74px;flex-wrap:wrap;gap:12px;padding:12px 16px}
        .topbar-left{width:100%}
        .topbar-right{margin-left:auto}
        .search-box-wrap{width:100%}
        .topbar-user-menu{right:auto;left:0;width:min(320px,calc(100vw - 32px))}
    }

    .clients-page{padding:24px 34px 34px;background:transparent;color:#F8FAFC}
    .page-title{font-size:28px;font-weight:800;color:#F8FAFC;margin-bottom:4px}
    .page-subtitle{font-size:14px;color:#94A3B8}
    .clients-header{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:22px}
    .btn-new-client{height:44px;padding:0 22px;border-radius:10px;background:linear-gradient(90deg,#2563EB,#7C3AED);color:#fff;font-size:14px;font-weight:700;display:inline-flex;align-items:center;gap:10px;box-shadow:0 12px 26px rgba(37,99,235,.28);transition:all .2s ease-in-out;text-decoration:none}
    .btn-new-client:hover{transform:translateY(-1px);box-shadow:0 16px 32px rgba(37,99,235,.35)}
    .btn-new-client svg{width:16px;height:16px}
    .client-stats-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin-bottom:18px}
    .client-stat-card{min-height:116px;padding:18px 20px;border-radius:12px;background:linear-gradient(180deg,rgba(30,41,59,.94),rgba(15,23,42,.96));border:1px solid rgba(148,163,184,.16);box-shadow:0 16px 36px rgba(0,0,0,.18);display:flex;flex-direction:column;justify-content:space-between}
    .client-stat-card__top{display:flex;align-items:flex-start;gap:14px}
    .stat-icon{width:46px;height:46px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex:none;color:#fff;box-shadow:0 12px 24px rgba(0,0,0,.18)}
    .stat-icon svg{width:22px;height:22px}
    .icon-blue{background:linear-gradient(135deg,#2563EB,#3B82F6)}
    .icon-green{background:linear-gradient(135deg,#15803D,#22C55E)}
    .icon-purple{background:linear-gradient(135deg,#6D28D9,#8B5CF6)}
    .icon-orange{background:linear-gradient(135deg,#F97316,#F59E0B)}
    .icon-teal{background:linear-gradient(135deg,#0F766E,#14B8A6)}
    .stat-copy{min-width:0}
    .stat-label{font-size:13px;font-weight:700;color:#E2E8F0;margin-bottom:8px}
    .stat-value{font-size:26px;font-weight:800;color:#fff;line-height:1}
    .stat-trend{font-size:13px;color:#94A3B8}
    .client-filters{display:grid;grid-template-columns:2fr .8fr .8fr .8fr auto auto;gap:12px;padding:14px 16px;border-radius:12px;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.16);margin-bottom:14px;align-items:center}
    .client-export-selection{display:flex;align-items:center;gap:10px;color:#94A3B8;font-size:13px;margin:0 0 12px 2px}
    .client-export-selection input{width:16px;height:16px;accent-color:#3B82F6}
    .filter-input,.filter-select{height:40px;border-radius:9px;background:rgba(15,23,42,.90);border:1px solid rgba(148,163,184,.18);color:#CBD5E1;padding:0 14px;font-size:13px;width:100%}
    .filter-input::placeholder{color:#64748B}
    .filter-input:focus,.filter-select:focus{outline:none;border-color:#3B82F6;box-shadow:0 0 0 3px rgba(59,130,246,.14)}
    .filter-input-wrap{position:relative}
    .filter-input-wrap svg{position:absolute;right:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#64748B;pointer-events:none}
    .btn-export,.btn-filters{height:40px;padding:0 18px;border-radius:9px;background:rgba(15,23,42,.90);border:1px solid rgba(59,130,246,.35);color:#3B82F6;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:8px;text-decoration:none;white-space:nowrap}
    .btn-filters{padding:0 12px;justify-content:center}
    .btn-export svg,.btn-filters svg{width:16px;height:16px}
    .clients-table-card{border-radius:12px;background:linear-gradient(180deg,rgba(30,41,59,.94),rgba(15,23,42,.96));border:1px solid rgba(148,163,184,.16);box-shadow:0 16px 36px rgba(0,0,0,.18);overflow:hidden}
    .clients-table{width:100%;border-collapse:collapse;min-width:1180px}
    .clients-table thead{background:rgba(15,23,42,.54)}
    .clients-table th{padding:14px 16px;text-align:left;color:#E2E8F0;font-size:13px;font-weight:700}
    .clients-table td{padding:14px 16px;color:#CBD5E1;font-size:13px;border-top:1px solid rgba(148,163,184,.10);vertical-align:middle}
    .clients-table tbody tr:hover{background:rgba(59,130,246,.05)}
    .client-info{display:flex;align-items:center;gap:12px}
    .client-avatar{width:38px;height:38px;border-radius:999px;object-fit:cover;object-position:center center;border:2px solid rgba(255,255,255,.12);display:block;flex-shrink:0;overflow:hidden;font-size:13px;font-weight:800;color:#fff;background-size:cover;background-position:center center}
    .client-name{font-size:14px;font-weight:700;color:#F8FAFC}
    .client-subtitle{font-size:12px}
    .sub-blue{color:#60A5FA}
    .sub-green{color:#4ADE80}
    .sub-purple{color:#C084FC}
    .sub-red{color:#F87171}
    .badge{display:inline-flex;align-items:center;height:24px;padding:0 10px;border-radius:7px;font-size:12px;font-weight:700;gap:6px}
    .badge-purple{color:#C084FC;background:rgba(124,58,237,.20)}
    .badge-blue{color:#60A5FA;background:rgba(37,99,235,.20)}
    .badge-green{color:#4ADE80;background:rgba(34,197,94,.18)}
    .badge-red{color:#F87171;background:rgba(239,68,68,.18)}
    .status-dot{width:7px;height:7px;border-radius:999px;background:currentColor}
    .status-active{color:#4ADE80;background:rgba(34,197,94,.18)}
    .status-inactive{color:#F87171;background:rgba(239,68,68,.18)}
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
    .crud-alert{margin-bottom:14px;padding:12px 14px;border-radius:10px;background:rgba(59,130,246,.12);border:1px solid rgba(59,130,246,.24);color:#BFDBFE;font-size:14px}
    .client-modal-backdrop{position:fixed;inset:0;background:rgba(2,6,23,.78);z-index:70;display:flex;align-items:center;justify-content:center;padding:20px}
    .client-modal{width:min(920px,100%);max-height:90vh;overflow:auto;border-radius:18px;background:linear-gradient(180deg,rgba(15,23,42,.98),rgba(8,17,31,.98));border:1px solid rgba(124,58,237,.18);box-shadow:0 32px 80px rgba(0,0,0,.45);color:#E2E8F0}
    .client-modal__head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:22px 22px 14px;border-bottom:1px solid rgba(148,163,184,.14)}
    .client-modal__head h2{font-size:22px;font-weight:800;color:#fff}
    .client-modal__head p{margin-top:6px;color:#94A3B8;font-size:14px}
    .client-modal__close{width:38px;height:38px;border-radius:10px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.72);color:#E2E8F0;display:grid;place-items:center}
    .client-modal__body{padding:18px 22px 22px}
    .client-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .client-form-field{display:grid;gap:8px}
    .client-form-field span{font-size:13px;font-weight:700;color:#E2E8F0}
    .client-form-field input,.client-form-field select,.client-form-field textarea{height:42px;border-radius:10px;background:rgba(15,23,42,.90);border:1px solid rgba(148,163,184,.18);color:#E2E8F0;padding:0 14px;font-size:13px}
    .client-form-field textarea{height:92px;padding:12px 14px;resize:vertical}
    .client-form-field input:focus,.client-form-field select:focus,.client-form-field textarea:focus{outline:none;border-color:#3B82F6;box-shadow:0 0 0 3px rgba(59,130,246,.14)}
    .client-photo-preview{width:88px;height:88px;border-radius:18px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.85);object-fit:cover;object-position:center center;display:block;margin-top:8px}
    .client-form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px;flex-wrap:wrap}
    .btn-secondary{height:42px;padding:0 18px;border-radius:10px;background:rgba(15,23,42,.76);border:1px solid rgba(148,163,184,.16);color:#CBD5E1;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}
    .btn-primary{height:42px;padding:0 18px;border-radius:10px;background:linear-gradient(90deg,#2563EB,#7C3AED);border:0;color:#fff;font-weight:700;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 12px 26px rgba(37,99,235,.22)}
    .client-form-errors{font-size:12px;color:#F87171}
    .modal-fade-enter{transition:opacity .2s ease, transform .2s ease}
    .modal-fade-enter-start{opacity:0;transform:scale(.98)}
    .modal-fade-enter-end{opacity:1;transform:scale(1)}
    @media (max-width:1280px){.client-stats-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.client-filters{grid-template-columns:1fr 1fr 1fr 1fr auto auto}}
    @media (max-width:1024px){.clients-page{padding:20px 16px 28px}.client-stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.client-filters{grid-template-columns:1fr 1fr}.client-modal{width:min(760px,100%)}.client-form-grid{grid-template-columns:1fr}}
    @media (max-width:768px){.clients-header{flex-direction:column;align-items:flex-start}.client-stats-grid{grid-template-columns:1fr}.client-filters{grid-template-columns:1fr}.client-modal__head{padding:18px}.client-modal__body{padding:16px 18px 18px}}
    .dashboard-shell{background:radial-gradient(circle at 12% 10%,rgba(124,58,237,.10),transparent 24%),radial-gradient(circle at 88% 4%,rgba(37,99,235,.10),transparent 26%),linear-gradient(180deg,#EEF3FF 0%,#F8FBFF 100%);color:#10213F}
    .sidebar{background:linear-gradient(180deg,rgba(255,255,255,.92),rgba(248,250,252,.96));border-right:1px solid #D8DDF2;box-shadow:0 18px 44px rgba(70,80,140,.08)}
    .logo-text,.sidebar-link,.sidebar-footer__meta strong,.sidebar-footer__meta p,.sidebar-collapse,.topbar-toggle,.topbar-bell,.topbar-user__meta strong{color:#10213F}
    .logo-text span,.sidebar-brand__mark,.sidebar-link.active,.btn-new-client,.btn-primary,.btn-register,.page-btn.active,.topbar-bell__badge{background:linear-gradient(90deg,#7C3AED,#2563EB);color:#fff}
    .sidebar-brand__mark{box-shadow:0 12px 24px rgba(124,58,237,.18)}
    .sidebar-collapse,.topbar-toggle,.topbar-bell,.action-btn,.page-btn,.btn-secondary,.filter-input,.filter-select,.btn-export,.btn-filters,.client-modal__close,.topbar-user-menu__action{background:rgba(255,255,255,.82);border-color:#D8DDF2;color:#10213F}
    .sidebar-link:hover,.action-btn:hover,.page-btn:hover,.btn-secondary:hover,.btn-export:hover,.btn-filters:hover,.topbar-user-menu__action:hover{background:rgba(255,255,255,.98)}
    .sidebar-link{color:#53637D}
    .sidebar-link.active{box-shadow:0 12px 28px rgba(37,99,235,.18)}
    .sidebar-footer{border-top:1px solid #D8DDF2}
    .sidebar-footer__car{filter:drop-shadow(0 18px 18px rgba(70,80,140,.12))}
    .sidebar-footer__meta span,.page-subtitle,.crud-muted,.stat-trend,.client-subtitle,.table-footer,.topbar-user__meta span,.topbar-user-menu__head span,.topbar-user-menu__section-label,.topbar-user-menu__pill{color:#64748B}
    .sidebar-overlay{background:rgba(16,33,63,.16)}
    .topbar{border-bottom:1px solid #D8DDF2;background:rgba(255,255,255,.74);backdrop-filter:blur(16px);box-shadow:0 12px 30px rgba(70,80,140,.06)}
    .search-box,.global-search{background:rgba(255,255,255,.88);border-color:#D8DDF2;color:#10213F}
    .search-box::placeholder,.global-search::placeholder{color:#94A3B8}
    .search-box-icon,.global-search-icon,.topbar-user__chevron{color:#94A3B8}
    .topbar-user{border-left:1px solid #D8DDF2}
    .topbar-user__avatar{box-shadow:0 12px 24px rgba(124,58,237,.18)}
    .topbar-user-menu{background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(248,250,252,.94));border:1px solid #D8DDF2;box-shadow:0 26px 52px rgba(70,80,140,.16);color:#10213F}
    .topbar-user-menu__section{border-color:#E5E7F5}
    .topbar-user-menu__pill{background:rgba(124,58,237,.10);color:#6D28D9}
    .topbar-user-menu__action--danger{color:#B91C1C}
    .clients-page{color:#10213F}
    .page-title,.client-name,.client-modal__head h2,.client-form-field span,.sales-table th,.sales-table td,.vehicle-name,.vehicle-detail-main h2,.profile-section__head h2,.panel-title,.crud-hero h1,.crud-panel h2,.crud-row strong,.kpi-value,.panel-card__title,.section-head h3,.hero-panel__title,.topbar-user__meta strong,.topbar-user-menu__head strong{color:#10213F}
    .client-stat-card,.clients-table-card,.client-modal,.panel-card,.sale-kpi-card,.sale-detail-card,.vehicle-detail-card,.profile-card,.crud-hero,.crud-panel,.kpi-card,.hero-panel,.summary-item,.spotlight-card,.movement-item,.alert-item,.sales-action-ghost,.sales-table thead,.sales-table td,.sales-filter-bar,.vehicle-form-card,.vehicle-preview-card,.vehicle-tip-card,.parking-kpi-card,.parking-map-card,.parking-list-card,.parking-status-card,.modal-card{background:rgba(255,255,255,.88);border-color:#D8DDF2;box-shadow:0 18px 44px rgba(70,80,140,.08)}
    .client-filters,.filter-input,.filter-select,.client-form-field input,.client-form-field select,.client-form-field textarea,.sale-form .sale-field input,.sale-form .sale-field select,.sale-form .sale-field textarea,.vehicle-form .vehicle-field input,.vehicle-form .vehicle-field select,.vehicle-form .vehicle-field textarea,.profile-field input,.parking-search,.parking-state-filter,.map-filter{background:rgba(255,255,255,.88);border-color:#D8DDF2;color:#10213F}
    .client-table-card,.clients-table thead,.clients-table td,.sales-table th,.sales-table td,.vehicle-table thead,.vehicle-table td,.summary-item,.chart-stat,.movement-item,.alert-item,.recent-entry-row,.parking-slot,.page-btn,.icon-btn,.btn-secondary-parking,.modal-secondary{border-color:#E5E7F5}
    .client-export-selection,.sale-export-selection,.panel-subtext,.crud-muted,.auth-card__footnote{color:#64748B}
    .crud-alert{background:rgba(37,99,235,.10);border-color:rgba(37,99,235,.20);color:#1D4ED8}
    .password-rules,.auth-password-box,.auth-code-box,.client-help-card,.sidebar-help-card{background:rgba(255,255,255,.88);border-color:#D8DDF2}
    .password-rule,.password-rules li,.auth-rule-list li,.auth-card__footnote,.auth-footer{color:#64748B}
    .password-rule--unmet{color:#F97316}
    .password-rule--met{color:#10B981}
    .password-rule--unmet svg,.password-rule--met svg{color:currentColor}
    @media (max-width: 768px){.topbar{background:rgba(255,255,255,.92)}}
    </style>
