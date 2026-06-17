<x-guest-layout>
    <div class="auth-stack auth-stack--login">
        <header class="auth-brand-panel">
            <img src="{{ asset('resources/img_empresa/logo_vehipark.svg') }}" alt="VehiPark" class="auth-brand-logo">
            <div class="auth-brand-copy">
                <h1 class="brand-name"><span class="vehi">Vehi</span><span class="park">Park</span></h1>
                <p class="brand-subtitle">Recuperación segura de contraseña</p>
            </div>
        </header>

        <section class="auth-card login-card">
            <div class="auth-card__title">
                <h2>Recuperar contraseña</h2>
                <p>Escribe tu correo y te mostraremos un código de recuperación</p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            @if (session('password_reset_code_visible'))
                <div class="password-rules mb-5">
                    <div class="password-rules__title">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2 4 5.5V12c0 5.1 3.6 9.7 8 10 4.4-.3 8-4.9 8-10V5.5L12 2Z" stroke="currentColor" stroke-width="1.8"/><path d="M9.2 12.4 11 14.2l3.8-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>Tu código de recuperación</span>
                    </div>
                    <div class="auth-code-box">
                        <span class="auth-code-box__label">Código</span>
                        <strong class="auth-code-box__value">{{ session('password_reset_code_visible') }}</strong>
                        <p class="auth-code-box__hint">Cópialo y pégalo abajo para activar el cambio de contraseña.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('password.verify') }}" class="auth-form mb-5">
                    @csrf

                    <input type="hidden" name="email" value="{{ old('email', session('password_recovery.email')) }}">

                    <div class="auth-field">
                        <label for="code">Código de 6 dígitos</label>
                        <div class="auth-input-wrap">
                            <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 7h10v10H7z" stroke="currentColor" stroke-width="1.8"/><path d="M9 9h6M9 12h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            <input id="code" class="input-auth" type="text" name="code" inputmode="numeric" maxlength="6" placeholder="123456" required>
                        </div>
                        @error('code')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-auth-primary">Verificar código</button>
                </form>
            @endif

            <div class="password-rules mb-5">
                <div class="password-rules__title">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2 4 5.5V12c0 5.1 3.6 9.7 8 10 4.4-.3 8-4.9 8-10V5.5L12 2Z" stroke="currentColor" stroke-width="1.8"/><path d="M9.2 12.4 11 14.2l3.8-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Usa el correo con el que te registraste</span>
                </div>
                <div class="password-rules-grid">
                    <div class="password-rule"><svg viewBox="0 0 20 20" fill="none"><path d="M10 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" stroke="currentColor" stroke-width="1.6"/><path d="m7 10 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Te mostraré un código en pantalla</div>
                    <div class="password-rule"><svg viewBox="0 0 20 20" fill="none"><path d="M10 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" stroke="currentColor" stroke-width="1.6"/><path d="m7 10 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Lo pegas para continuar</div>
                    <div class="password-rule"><svg viewBox="0 0 20 20" fill="none"><path d="M10 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" stroke="currentColor" stroke-width="1.6"/><path d="m7 10 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Luego creas la nueva clave</div>
                    <div class="password-rule"><svg viewBox="0 0 20 20" fill="none"><path d="M10 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" stroke="currentColor" stroke-width="1.6"/><path d="m7 10 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Y vuelves al login</div>
                </div>
            </div>

            <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                @csrf

                <div class="auth-field">
                    <label for="email">Email</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="1.8"/><path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <input id="email" class="input-auth" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@gmail.com">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-auth-primary">Enviar enlace de recuperación</button>

                <p class="auth-card__footnote">
                    ¿Recordaste tu contraseña?
                    <a href="{{ route('login') }}" class="auth-link">Volver al login</a>
                </p>
            </form>
        </section>

        <footer class="auth-footer">© 2024 VehiPark. Todos los derechos reservados.</footer>
    </div>
</x-guest-layout>
