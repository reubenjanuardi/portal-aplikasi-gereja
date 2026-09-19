<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');
});

test('users authenticating via inertia are redirected via inertia location', function () {
    $user = User::factory()->create();

    $response = $this->withHeaders(['X-Inertia' => 'true'])->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertStatus(409);
    $response->assertHeader('X-Inertia-Location', url('/dashboard'));
});

test('users accessing keuangan before login are redirected to keuangan via inertia location upon login', function () {
    $user = User::factory()->create();

    // Guest attempts to visit /keuangan
    $this->get('/keuangan');

    // Guest authenticates via Inertia login form
    $response = $this->withHeaders(['X-Inertia' => 'true'])->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertStatus(409);
    $response->assertHeader('X-Inertia-Location', url('/keuangan'));
});

test('users accessing settings before login are redirected to settings via inertia location upon login', function () {
    $user = User::factory()->create();

    // Guest attempts to visit /settings
    $this->get('/settings');

    // Guest authenticates via Inertia login form
    $response = $this->withHeaders(['X-Inertia' => 'true'])->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertStatus(409);
    $response->assertHeader('X-Inertia-Location', url('/settings'));
});

test('users accessing profile before login are redirected to profile via inertia location upon login', function () {
    $user = User::factory()->create();

    // Guest attempts to visit /profile
    $this->get('/profile');

    // Guest authenticates via Inertia login form
    $response = $this->withHeaders(['X-Inertia' => 'true'])->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertStatus(409);
    $response->assertHeader('X-Inertia-Location', url('/profile'));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('users logging out from filament panel keuangan are redirected cleanly to landing page', function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $admin = User::where('email', 'admin@gpibhosiana.org')->first();

    $response = $this->actingAs($admin)->post('/keuangan/logout');

    $response->assertRedirect('/');
});

test('users logging out from filament panel settings are redirected cleanly to landing page', function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $admin = User::where('email', 'admin@gpibhosiana.org')->first();

    $response = $this->actingAs($admin)->post('/settings/logout');

    $response->assertRedirect('/');
});

test('public registration screen is not available', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});

test('public registration endpoint is not available', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertNotFound();
});

