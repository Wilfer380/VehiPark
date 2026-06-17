<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $code = (string) random_int(100000, 999999);

        $user = User::query()->where('email', $request->string('email')->toString())->firstOrFail();

        session([
            'password_recovery' => [
                'email' => $user->email,
                'code' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10)->timestamp,
            ],
            'password_reset_code_visible' => $code,
        ]);

        return back()->withInput($request->only('email'))->with('status', 'Se generó tu código de recuperación. Revísalo en la ventana siguiente.');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $recovery = session('password_recovery');

        if (! is_array($recovery) || ($recovery['email'] ?? null) !== $request->string('email')->toString()) {
            return back()->withErrors(['code' => 'No hay un código válido para ese correo.']);
        }

        if (($recovery['expires_at'] ?? 0) < now()->timestamp) {
            session()->forget(['password_recovery', 'password_reset_code_visible']);

            return back()->withErrors(['code' => 'El código expiró. Solicita uno nuevo.']);
        }

        if (($recovery['attempts'] ?? 0) >= 5) {
            session()->forget(['password_recovery', 'password_reset_code_visible']);

            return back()->withErrors(['code' => 'Demasiados intentos. Genera un nuevo código.']);
        }

        if (! Hash::check($request->string('code')->toString(), (string) ($recovery['code'] ?? ''))) {
            $recovery['attempts'] = (int) ($recovery['attempts'] ?? 0) + 1;
            session(['password_recovery' => $recovery]);

            return back()->withErrors(['code' => 'Código incorrecto.']);
        }

        session([
            'password_reset_verified_email' => $recovery['email'],
        ]);

        session()->forget(['password_recovery', 'password_reset_code_visible']);

        return redirect()->route('password.reset')->with('status', 'Código verificado. Ya puedes crear una nueva contraseña.');
    }
}
