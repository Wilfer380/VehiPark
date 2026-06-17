<?php

use App\Models\User;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password code can be requested', function () {
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    $this->assertNotEmpty(session('password_reset_code_visible'));
    $this->assertSame($user->email, session('password_recovery.email'));
});

test('reset password screen can be rendered after code verification', function () {
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    $code = session('password_reset_code_visible');

    $this->post('/forgot-password/verify', [
        'email' => $user->email,
        'code' => $code,
    ])->assertRedirect(route('password.reset'));

    $this->get('/reset-password')->assertStatus(200);
});

test('password can be reset with valid code', function () {
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    $code = session('password_reset_code_visible');

    $this->post('/forgot-password/verify', [
        'email' => $user->email,
        'code' => $code,
    ]);

    $response = $this->post('/reset-password', [
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('login'));
});
