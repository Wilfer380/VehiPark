<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\User;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        $email = session('password_reset_verified_email');

        abort_unless(is_string($email) && $email !== '', 403);

        return view('auth.reset-password', ['email' => $email]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $email = session('password_reset_verified_email');

        abort_unless(is_string($email) && $email !== '', 403);

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'No se encontró el usuario.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->string('password')->toString()),
            'remember_token' => null,
        ])->save();

        session()->forget(['password_reset_verified_email', 'password_recovery', 'password_reset_code_visible']);

        return redirect()->route('login')->with('status', 'Tu contraseña fue actualizada correctamente.');
    }
}
