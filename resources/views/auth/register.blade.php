<x-guest-layout>
    <div class="auth-stack auth-stack--register">
        <header class="auth-brand-panel">
            <img src="{{ asset('resources/img_empresa/logo_vehipark.svg') }}" alt="VehiPark" class="auth-brand-logo">
            <div class="auth-brand-copy">
                <h1 class="brand-name"><span class="vehi">Vehi</span><span class="park">Park</span></h1>
                <p class="brand-subtitle">Crea tu cuenta para empezar</p>
            </div>
        </header>

        <section class="auth-card register-card">
            <div class="auth-card__title">
                <h2>Crear cuenta</h2>
                <p>Completa el formulario para registrarte</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="register-grid" x-data="{ showPassword: false, showConfirmation: false }">
                @csrf

                <div class="auth-field">
                    <label for="name">Nombre completo</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="1.8"/><path d="M4 20c1.5-3.6 4.4-5.5 8-5.5S18.5 16.4 20 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <input id="name" class="input-register" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Ingresa tu nombre completo">
                    </div>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="email">Correo electrónico</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="1.8"/><path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <input id="email" class="input-register" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="ejemplo@vehipark.com">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="phone">Teléfono</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 5c1.2 3 2.9 5.4 6 8l2-2.2c.5-.5 1.2-.6 1.8-.3l2.5 1.3c.9.5 1.3 1.5.9 2.5l-1 2.4c-.4 1-1.3 1.6-2.4 1.4C10 19.4 4.6 14 3.7 5.6 3.6 4.4 4.3 3.4 5.4 3l2.4-1c1-.4 2.1 0 2.5.9L11.6 5.4c.3.6.2 1.3-.3 1.8L9 9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <input id="phone" class="input-register" type="text" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="300 123 4567">
                    </div>
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="document">Documento de identidad</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5h16v14H4z" stroke="currentColor" stroke-width="1.8"/><path d="M8 8h8M8 12h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <input id="document" class="input-register" type="text" name="document" value="{{ old('document') }}" autocomplete="off" placeholder="CC o NIT">
                    </div>
                    @error('document')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password">Contraseña</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="16" r="1.4" fill="currentColor"/></svg>
                        <input id="password" class="input-register input-register--password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="new-password" placeholder="••••••••">
                        <button type="button" class="auth-input-toggle" @click="showPassword = !showPassword" aria-label="Mostrar contraseña">
                            <svg x-show="!showPassword" viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2.8" stroke="currentColor" stroke-width="1.8"/></svg>
                            <svg x-show="showPassword" viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true"><path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M10.6 10.6A2.8 2.8 0 0 0 12 15c1.5 0 2.8-1.3 2.8-2.8 0-.5-.1-1-.4-1.4" stroke="currentColor" stroke-width="1.8"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="16" r="1.4" fill="currentColor"/></svg>
                        <input id="password_confirmation" class="input-register input-register--password" :type="showConfirmation ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
                        <button type="button" class="auth-input-toggle" @click="showConfirmation = !showConfirmation" aria-label="Mostrar confirmación">
                            <svg x-show="!showConfirmation" viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2.8" stroke="currentColor" stroke-width="1.8"/></svg>
                            <svg x-show="showConfirmation" viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true"><path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M10.6 10.6A2.8 2.8 0 0 0 12 15c1.5 0 2.8-1.3 2.8-2.8 0-.5-.1-1-.4-1.4" stroke="currentColor" stroke-width="1.8"/></svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field register-grid__full">
                    <label for="role">Rol</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3l7 4v5c0 5-3.5 8.5-7 9.8C8.5 20.5 5 17 5 12V7l7-4Z" stroke="currentColor" stroke-width="1.8"/><path d="M8 12l2.2 2.2L16 8.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <select id="role" class="input-register pr-10" name="role" required>
                            <option value="">Selecciona un rol</option>
                            <option value="admin" @selected(old('role') === 'admin')>Administrador</option>
                            <option value="supervisor" @selected(old('role') === 'supervisor')>Supervisor</option>
                            <option value="empleado" @selected(old('role') === 'empleado')>Empleado</option>
                        </select>
                        <svg class="auth-input-toggle h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 8 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    @error('role')
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

                <label class="auth-check register-grid__full">
                    <input type="checkbox" name="terms" @checked(old('terms')) required>
                    <span>Acepto los <a href="#" class="auth-link">Términos y Condiciones</a> y la <a href="#" class="auth-link">Política de Privacidad</a></span>
                </label>
                @error('terms')
                    <p class="register-grid__full text-red-500 text-xs -mt-2">{{ $message }}</p>
                @enderror

                <button type="submit" class="btn-register register-grid__full">Crear cuenta</button>

                <p class="auth-card__footnote register-grid__full">
                    ¿Ya tienes cuenta?
                    <a class="auth-link" href="{{ route('login') }}">Iniciar sesión</a>
                </p>
            </form>
        </section>

        <footer class="auth-footer">© 2024 VehiPark. Todos los derechos reservados.</footer>
    </div>
</x-guest-layout>
