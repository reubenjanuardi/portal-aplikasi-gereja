<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

test('forgot password screen is not available', function () {
    $response = $this->get('/forgot-password');

    $response->assertNotFound();
});

test('forgot password link cannot be requested via email endpoint', function () {
    $user = User::factory()->create();

    $response = $this->post('/forgot-password', ['email' => $user->email]);

    $response->assertNotFound();
});

test('reset password screen can be rendered with valid token', function () {
    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $response = $this->get('/reset-password/'.$token.'?email='.$user->email);

    $response->assertStatus(200);
});

test('password can be reset with valid token', function () {
    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('login'));

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

