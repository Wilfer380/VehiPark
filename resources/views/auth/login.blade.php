<x-guest-layout>
    <div class="auth-stack auth-stack--login">
        <header class="auth-brand-panel">
            <img src="{{ asset('resources/img_empresa/logo_vehipark.svg') }}" alt="VehiPark" class="auth-brand-logo">
            <div class="auth-brand-copy">
                <h1 class="brand-name"><span class="vehi">Vehi</span><span class="park">Park</span></h1>
                <p class="brand-subtitle">Sistema de Venta de Vehículos y Gestión de Parqueadero</p>
            </div>
        </header>

        <section class="auth-card login-card">
            <div class="auth-card__title">
                <h2>Iniciar sesión</h2>
                <p>Bienvenido, inicia sesión para continuar</p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="auth-form" x-data="{ showPassword: false, password: '' }">
                @csrf

                <div class="auth-field">
                    <label for="email">Correo electrónico</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="1.8"/><path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <input id="email" class="input-auth" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="ejemplo@vehipark.com">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password">Contraseña</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="16" r="1.4" fill="currentColor"/></svg>
                        <input id="password" class="input-auth input-auth--password" :type="showPassword ? 'text' : 'password'" name="password" x-model="password" required autocomplete="current-password" placeholder="••••••••">
                        <button type="button" class="auth-input-toggle" @click="showPassword = !showPassword" aria-label="Mostrar u ocultar contraseña">
                            <svg x-show="!showPassword" viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2.8" stroke="currentColor" stroke-width="1.8"/></svg>
                            <svg x-show="showPassword" viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true"><path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M10.6 10.6A2.8 2.8 0 0 0 12 15c1.5 0 2.8-1.3 2.8-2.8 0-.5-.1-1-.4-1.4" stroke="currentColor" stroke-width="1.8"/><path d="M6.8 6.8C4.7 8.2 3.3 10 2 12c1.9 3.1 5.4 7 10 7 1.8 0 3.4-.4 4.8-1.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                    <div class="password-rules auth-password-requirements" aria-live="polite">
                        <div class="password-rules__title auth-password-requirements__title">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2 4 5.5V12c0 5.1 3.6 9.7 8 10 4.4-.3 8-4.9 8-10V5.5L12 2Z" stroke="currentColor" stroke-width="1.8"/><path d="M9.2 12.4 11 14.2l3.8-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>La contraseña debe contener:</span>
                        </div>
                        <div class="password-rules-grid auth-password-requirements__grid">
                            <div class="password-rule" :class="password.length >= 6 ? 'password-rule--met' : 'password-rule--unmet'"><svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" stroke="currentColor" stroke-width="1.6"/><path d="m7 10 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Mínimo 6 caracteres</div>
                            <div class="password-rule" :class="/[a-z]/.test(password) ? 'password-rule--met' : 'password-rule--unmet'"><svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" stroke="currentColor" stroke-width="1.6"/><path d="m7 10 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Una letra minúscula</div>
                            <div class="password-rule" :class="/\d/.test(password) ? 'password-rule--met' : 'password-rule--unmet'"><svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" stroke="currentColor" stroke-width="1.6"/><path d="m7 10 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Un número</div>
                            <div class="password-rule" :class="/[^A-Za-z0-9\s]/.test(password) ? 'password-rule--met' : 'password-rule--unmet'"><svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" stroke="currentColor" stroke-width="1.6"/><path d="m7 10 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Un carácter especial</div>
                        </div>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-login-meta">
                    <label for="remember_me" class="auth-check">
                        <input id="remember_me" type="checkbox" name="remember" @checked(old('remember'))>
                        <span>Recordarme</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="auth-link" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                    @endif
                </div>

                <button type="submit" class="btn-auth-primary">
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true"><path d="M12 2a5 5 0 0 1 5 5v2h1.5A2.5 2.5 0 0 1 21 11.5v7A2.5 2.5 0 0 1 18.5 21h-13A2.5 2.5 0 0 1 3 18.5v-7A2.5 2.5 0 0 1 5.5 9H7V7a5 5 0 0 1 5-5Z" stroke="currentColor" stroke-width="1.8"/><path d="M9.5 9V7a2.5 2.5 0 1 1 5 0v2" stroke="currentColor" stroke-width="1.8"/></svg>
                    <span>Iniciar sesión</span>
                </button>

                <div class="auth-divider"><span>o continúa con</span></div>

                <div class="auth-social-grid">
                    <button type="button" class="social-button">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true"><path fill="#EA4335" d="M21.35 11.1h-9.17v2.8h5.26c-.23 1.34-1.62 3.93-5.26 3.93-3.17 0-5.77-2.63-5.77-5.83s2.6-5.83 5.77-5.83c1.8 0 3 .77 3.7 1.43l2.53-2.44C17.6 3.76 15.45 2.8 12.18 2.8 6.82 2.8 2.47 7.16 2.47 12.5s4.35 9.7 9.71 9.7c5.62 0 9.34-3.95 9.34-9.52 0-.64-.07-1.12-.17-1.58Z"/></svg>
                        <span>Google</span>
                    </button>
                    <button type="button" class="social-button">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true"><path fill="#00A4EF" d="M3 4.5 11 3v8H3V4.5Zm8 0 10-1.5V11H11V4.5ZM3 12h8v8.5L3 19V12Zm8 0h10v7.5L11 20V12Z"/></svg>
                        <span>Microsoft</span>
                    </button>
                </div>

                <p class="auth-card__footnote">
                    ¿No tienes cuenta?
                    <a href="{{ route('register') }}" class="auth-link">Regístrate aquí</a>
                </p>
            </form>
        </section>

        <footer class="auth-footer">© 2024 VehiPark. Todos los derechos reservados.</footer>
    </div>
</x-guest-layout>
