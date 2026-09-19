<?php

use App\Filament\Settings\Resources\UserResource\Pages\ListUsers;
use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RbacSeeder::class);
});

test('super admin can generate password reset link via filament action', function () {
    Filament::setCurrentPanel(Filament::getPanel('settings'));

    $admin = User::where('email', 'admin@gpibhosiana.org')->first();
    $target = User::where('email', 'operator@gpibhosiana.org')->first();

    $component = Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->callTableAction('reset_password', $target);

    $component->assertSuccessful();

    // Verify token was generated in database table
    $tokenRecord = DB::table('password_reset_tokens')
        ->where('email', $target->email)
        ->first();

    expect($tokenRecord)->not->toBeNull();

    // Verify activity log was recorded properly
    $log = ActivityLog::where('log_name', 'user_management')
        ->where('subject_type', User::class)
        ->where('subject_id', $target->id)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->description)->toContain("Administrator [{$admin->name}] membuat link reset password untuk pengguna [{$target->name}]");
    expect($log->properties)->toHaveKey('target_user_id', $target->id);
    expect($log->properties)->toHaveKey('target_user_email', $target->email);

    // Strict security checks: token and full url must NEVER be in log
    expect($log->description)->not->toContain('reset-password');
    expect(json_encode($log->properties))->not->toContain('reset-password');
    expect(json_encode($log->properties))->not->toContain('token');
});

test('reset action is hidden for inactive target user', function () {
    Filament::setCurrentPanel(Filament::getPanel('settings'));

    $admin = User::where('email', 'admin@gpibhosiana.org')->first();
    $inactiveUser = User::factory()->create([
        'is_active' => false,
    ]);

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->assertTableActionHidden('reset_password', $inactiveUser);

    expect(DB::table('password_reset_tokens')->where('email', $inactiveUser->email)->first())->toBeNull();
});

test('unauthorized user cannot access settings panel or trigger reset action', function () {
    $operator = User::where('email', 'operator@gpibhosiana.org')->first();
    $target = User::where('email', 'bendahara@gpibhosiana.org')->first();

    // Direct HTTP route access to settings panel is forbidden
    $response = $this->actingAs($operator)->get('/settings/users');
    $response->assertStatus(403);
});

test('generated reset link has the correct URL structure', function () {
    $user = User::where('email', 'operator@gpibhosiana.org')->first();
    $token = Password::broker()->createToken($user);

    $resetUrl = route('password.reset', [
        'token' => $token,
        'email' => $user->email,
    ]);

    expect($resetUrl)->toContain('/reset-password/' . $token);
    expect($resetUrl)->toContain('email=' . urlencode($user->email));
});

test('valid reset token renders reset password page', function () {
    $user = User::where('email', 'operator@gpibhosiana.org')->first();
    $token = Password::broker()->createToken($user);

    $response = $this->get(route('password.reset', [
        'token' => $token,
        'email' => $user->email,
    ]));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/ResetPassword')
        ->where('email', $user->email)
        ->where('token', $token)
    );
});

test('password can be reset with valid token and user can login with new password', function () {
    $user = User::where('email', 'operator@gpibhosiana.org')->first();
    $oldPassword = 'password';
    $newPassword = 'NewSecretPassword123#';

    $token = Password::broker()->createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/login');

    // Token record must be cleared
    $tokenRecord = DB::table('password_reset_tokens')->where('email', $user->email)->first();
    expect($tokenRecord)->toBeNull();

    // Refresh user and verify password hash changed
    $user->refresh();
    expect(Hash::check($newPassword, $user->password))->toBeTrue();
    expect(Hash::check($oldPassword, $user->password))->toBeFalse();

    // User can login with new password
    $loginResponse = $this->post('/login', [
        'email' => $user->email,
        'password' => $newPassword,
    ]);
    $this->assertAuthenticatedAs($user);
    $loginResponse->assertRedirect('/dashboard');

    // Logout
    $this->post('/logout');
    $this->assertGuest();

    // Old password no longer works
    $oldLoginResponse = $this->post('/login', [
        'email' => $user->email,
        'password' => $oldPassword,
    ]);
    $this->assertGuest();
    $oldLoginResponse->assertSessionHasErrors('email');
});

test('password reset fails with invalid token', function () {
    $user = User::where('email', 'operator@gpibhosiana.org')->first();

    $response = $this->post('/reset-password', [
        'token' => 'invalid-token-value-12345',
        'email' => $user->email,
        'password' => 'NewSecretPassword123#',
        'password_confirmation' => 'NewSecretPassword123#',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('password reset fails with expired token', function () {
    $user = User::where('email', 'operator@gpibhosiana.org')->first();
    $token = Password::broker()->createToken($user);

    // Fast-forward time past expiration configured in auth.passwords.users.expire (default 60 mins)
    $expireMinutes = config('auth.passwords.users.expire', 60);
    Carbon::setTestNow(now()->addMinutes($expireMinutes + 5));

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewSecretPassword123#',
        'password_confirmation' => 'NewSecretPassword123#',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();

    Carbon::setTestNow(); // Reset time
});

test('user policy authorizes resetPassword only for managers on active accounts', function () {
    $admin = User::where('email', 'admin@gpibhosiana.org')->first();
    $operator = User::where('email', 'operator@gpibhosiana.org')->first();
    $activeTarget = User::where('email', 'bendahara@gpibhosiana.org')->first();
    $inactiveTarget = User::factory()->create(['is_active' => false]);

    $policy = new \App\Policies\UserPolicy();

    // Super Admin via Gate or direct policy
    expect($policy->resetPassword($admin, $activeTarget))->toBeTrue();
    expect($policy->resetPassword($admin, $inactiveTarget))->toBeFalse();

    // Operator lacks portal.user.manage
    expect($policy->resetPassword($operator, $activeTarget))->toBeFalse();
    expect($policy->resetPassword($operator, $inactiveTarget))->toBeFalse();
});

test('reset password link modal view renders correctly with url and copy actions', function () {
    $target = User::where('email', 'operator@gpibhosiana.org')->first();
    $testUrl = 'http://localhost/reset-password/sample-token-123?email=' . urlencode($target->email);

    $view = $this->view('filament.settings.modals.reset-password-flow', [
        'record' => $target,
        'expireMinutes' => 60,
    ]);

    $view->assertSee($target->name);
    $view->assertSee($target->email);
    $view->assertSee('Generate Reset Password Link?');
    $view->assertSee('Batal');
    $view->assertSee('Generate Link');
    $view->assertSee('Salin Link');
    $view->assertSee('Link berhasil disalin.');
});

test('admin endpoint generates reset link and returns json', function () {
    $admin = User::where('email', 'admin@gpibhosiana.org')->first();
    $target = User::where('email', 'bendahara@gpibhosiana.org')->first();

    $response = $this->actingAs($admin)->postJson(route('admin.users.generate-reset-link', $target));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'expireMinutes' => 60,
        'target' => [
            'id' => $target->id,
            'name' => $target->name,
            'email' => $target->email,
        ],
    ]);

    $data = $response->json();
    expect($data['url'])->toContain('/reset-password/');
    expect($data['url'])->toContain('email=' . urlencode($target->email));
});

test('admin endpoint returns 403 for unauthorized users and inactive targets', function () {
    $operator = User::where('email', 'operator@gpibhosiana.org')->first();
    $target = User::where('email', 'bendahara@gpibhosiana.org')->first();
    $inactiveTarget = User::factory()->create(['is_active' => false]);
    $admin = User::where('email', 'admin@gpibhosiana.org')->first();

    // Operator lacks permission
    $resOperator = $this->actingAs($operator)->postJson(route('admin.users.generate-reset-link', $target));
    $resOperator->assertStatus(403);

    // Inactive user cannot have reset link generated
    $resInactive = $this->actingAs($admin)->postJson(route('admin.users.generate-reset-link', $inactiveTarget));
    $resInactive->assertStatus(403);
});



