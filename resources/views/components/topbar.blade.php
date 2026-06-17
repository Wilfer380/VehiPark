@php
    $user = auth()->user();
    $displayName = request()->routeIs('parqueadero.*') ? 'Maicol Oliveras' : ($user?->name ?? 'Usuario interno');
    $displayRole = request()->routeIs('parqueadero.*') ? 'Empleado' : match ($user?->role) {
        'admin' => 'Administrador',
        'supervisor' => 'Supervisor',
        'empleado' => 'Empleado',
        default => 'Usuario interno',
    };
    $searchPlaceholder = request()->routeIs('parqueadero.*') ? 'Buscar placas, clientes o ubicaciones…' : 'Buscar en VehiPark…';
    $initials = request()->routeIs('parqueadero.*')
        ? 'MO'
        : collect(explode(' ', trim((string) ($user?->name ?? 'VP'))))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->implode('');
    $avatarUrl = request()->routeIs('parqueadero.*') ? null : ($user?->avatar ? route('profile.avatar', $user) : null);
@endphp

<style>
    .topbar-right{display:flex;align-items:center;gap:14px;flex:none}
    .global-search-wrapper{width:320px;max-width:320px;position:relative}
    .global-search{width:100%;height:40px;border-radius:10px;background:rgba(15,23,42,.85);border:1px solid rgba(148,163,184,.18);color:#CBD5E1;padding:0 42px 0 14px;font-size:14px;outline:none}
    .global-search::placeholder{color:#64748B}
    .global-search:focus{outline:none;border-color:#3B82F6;box-shadow:0 0 0 3px rgba(59,130,246,.14)}
    .global-search-icon{position:absolute;right:14px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:#94A3B8;pointer-events:none}
    .global-search-toggle{display:none;width:40px;height:40px;border-radius:10px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.85);color:#CBD5E1;align-items:center;justify-content:center}
    .global-search-toggle svg{width:18px;height:18px}
    .topbar-user__avatar,.topbar-user-menu__avatar{width:42px;height:42px;border-radius:9999px;background:linear-gradient(135deg,#7c3aed,#3b82f6);display:grid;place-items:center;font-weight:800;color:#fff;box-shadow:0 12px 24px rgba(124,58,237,.18);overflow:hidden;flex:none}
    .topbar-user-menu__avatar{width:44px;height:44px;box-shadow:0 12px 24px rgba(124,58,237,.16)}
    .topbar-user__avatar img,.topbar-user-menu__avatar img{width:100%;height:100%;object-fit:cover;object-position:center center;display:block;border-radius:inherit}
    .topbar-user__avatar span,.topbar-user-menu__avatar span{line-height:1}
    @media (max-width: 1024px){.global-search-wrapper{width:260px;max-width:260px}}
    @media (max-width: 768px){.global-search-wrapper{width:auto;max-width:none}.global-search{display:none}.global-search-toggle{display:inline-flex}}
    .topbar{border-bottom:1px solid #D8DDF2;background:rgba(255,255,255,.74);backdrop-filter:blur(16px);box-shadow:0 12px 30px rgba(70,80,140,.06)}
    .topbar-toggle,.topbar-bell{background:rgba(255,255,255,.88);border:1px solid #D8DDF2;color:#10213F}
    .global-search-wrapper{width:320px;max-width:320px;position:relative}
    .global-search{width:100%;height:40px;border-radius:10px;background:rgba(255,255,255,.88);border:1px solid #D8DDF2;color:#10213F;padding:0 42px 0 14px;font-size:14px;outline:none}
    .global-search::placeholder{color:#94A3B8}
    .global-search:focus{outline:none;border-color:#7C3AED;box-shadow:0 0 0 3px rgba(124,58,237,.12)}
    .global-search-icon{position:absolute;right:14px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:#94A3B8;pointer-events:none}
    .global-search-toggle{display:none;width:40px;height:40px;border-radius:10px;border:1px solid #D8DDF2;background:rgba(255,255,255,.88);color:#10213F;align-items:center;justify-content:center}
    .topbar-user__avatar,.topbar-user-menu__avatar{box-shadow:0 12px 24px rgba(124,58,237,.18)}
    @media (max-width: 768px){.global-search{display:none}.global-search-toggle{display:inline-flex}}
    </style>

<header class="topbar" x-data="{ userMenuOpen: false }">
    <div class="topbar-left">
        <button type="button" class="topbar-toggle" @click="sidebarOpen = !sidebarOpen" aria-label="Abrir o cerrar menú">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 6h14M5 12h14M5 18h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </button>
    </div>

    <div class="topbar-right">
        <div class="global-search-wrapper">
            <input type="search" class="global-search" placeholder="{{ $searchPlaceholder }}" aria-label="{{ $searchPlaceholder }}">
            <svg class="global-search-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            <button type="button" class="global-search-toggle" aria-label="Abrir búsqueda global">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            </button>
        </div>

        <button class="topbar-bell" type="button" aria-label="Notificaciones">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4a5 5 0 0 0-5 5v2.8c0 .7-.2 1.4-.6 2L5 15.5h14l-1.4-1.7a3.3 3.3 0 0 1-.6-2V9a5 5 0 0 0-5-5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9.5 18a2.5 2.5 0 0 0 5 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            <span class="topbar-bell__badge">3</span>
        </button>

        <div class="topbar-user-wrap" @click.outside="userMenuOpen = false">
            <button class="topbar-user" type="button" @click="userMenuOpen = !userMenuOpen" aria-haspopup="menu" :aria-expanded="userMenuOpen.toString()">
                <div class="topbar-user__avatar topbar-user__avatar--circle">
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="Foto de perfil">
                    @else
                        <span>{{ $initials ?: 'VP' }}</span>
                    @endif
                </div>
                <div class="topbar-user__meta">
                    <strong>{{ $displayName }}</strong>
                    <span>{{ $displayRole }}</span>
                </div>
                <svg class="topbar-user__chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>

            <div x-show="userMenuOpen" x-cloak x-transition.origin.top.right class="topbar-user-menu" role="menu">
                <div class="topbar-user-menu__head">
                    <div class="topbar-user-menu__avatar topbar-user__avatar--circle">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Foto de perfil">
                        @else
                            <span>{{ $initials ?: 'VP' }}</span>
                        @endif
                    </div>
                    <div>
                        <strong>{{ $displayName }}</strong>
                        <span>{{ $user?->email ?? 'Sin correo' }}</span>
                    </div>
                </div>

                <div class="topbar-user-menu__section">
                    <span class="topbar-user-menu__section-label">Sesión actual</span>
                    <div class="topbar-user-menu__pill">{{ $displayRole }}</div>
                </div>

                <a href="{{ route('profile.edit') }}" class="topbar-user-menu__action" role="menuitem">
                    <span>Perfil</span>
                    <span>›</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="topbar-user-menu__logout">
                    @csrf
                    <button type="submit" class="topbar-user-menu__action topbar-user-menu__action--danger" role="menuitem">
                        <span>Salir</span>
                        <span>↗</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
