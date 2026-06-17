<x-guest-layout>
    <div class="auth-stack auth-stack--register">
        <header class="auth-brand-panel">
            <img src="{{ asset('resources/img_empresa/logo_vehipark.svg') }}" alt="VehiPark" class="auth-brand-logo">
            <div class="auth-brand-copy">
                <h1 class="brand-name"><span class="vehi">Vehi</span><span class="park">Park</span></h1>
                <p class="brand-subtitle">Restablece tu contraseña</p>
            </div>
        </header>

        <section class="auth-card register-card">
            <div class="auth-card__title">
                <h2>Nueva contraseña</h2>
                <p>Escribe tu correo y la nueva clave para continuar</p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="register-grid">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="auth-field register-grid__full">
                    <label for="email">Email</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="1.8"/><path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <input id="email" class="input-register" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" placeholder="admin@gmail.com">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password">Contraseña</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="16" r="1.4" fill="currentColor"/></svg>
                        <input id="password" class="input-register input-register--password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••">
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="16" r="1.4" fill="currentColor"/></svg>
                        <input id="password_confirmation" class="input-register input-register--password" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
                    </div>
                    @error('password_confirmation')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="password-rules register-grid__full">
                    <div class="password-rules__title">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2 4 5.5V12c0 5.1 3.6 9.7 8 10 4.4-.3 8-4.9 8-10V5.5L12 2Z" stroke="currentColor" stroke-width="1.8"/><path d="M9.2 12.4 11 14.2l3.8-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>La contraseña debe contener:</span>
                    </div>
                    <div class="password-rules-grid">
                        <div class="password-rule"><svg viewBox="0 0 20 20" fill="none"><path d="M10 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" stroke="currentColor" stroke-width="1.6"/><path d="m7 10 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Mínimo 8 caracteres</div>
                        <div class="password-rule"><svg viewBox="0 0 20 20" fill="none"><path d="M10 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" stroke="currentColor" stroke-width="1.6"/><path d="m7 10 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Una letra mayúscula</div>
                        <div class="password-rule"><svg viewBox="0 0 20 20" fill="none"><path d="M10 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" stroke="currentColor" stroke-width="1.6"/><path d="m7 10 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Un número</div>
                        <div class="password-rule"><svg viewBox="0 0 20 20" fill="none"><path d="M10 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" stroke="currentColor" stroke-width="1.6"/><path d="m7 10 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Un carácter especial</div>
                    </div>
                </div>

                <button type="submit" class="btn-register register-grid__full">Restablecer contraseña</button>

                <p class="auth-card__footnote register-grid__full">
                    ¿Volver al inicio?
                    <a class="auth-link" href="{{ route('login') }}">Iniciar sesión</a>
                </p>
            </form>
        </section>

        <footer class="auth-footer">© 2024 VehiPark. Todos los derechos reservados.</footer>
    </div>
</x-guest-layout>
