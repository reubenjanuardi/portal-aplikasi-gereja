<?php

use App\Models\ActivityLog;
use App\Models\ChartOfAccount;
use App\Models\User;
use App\Models\Voucher;
use Database\Seeders\ChartOfAccountSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->seed(ChartOfAccountSeeder::class);
    $this->seed(RbacSeeder::class);
});

test('super admin can access both keuangan and settings panels and all operations', function () {
    $admin = User::where('email', 'admin@gpibhosiana.org')->first();

    expect($admin->canAccessModule('keuangan'))->toBeTrue()
        ->and($admin->canAccessModule('settings'))->toBeTrue()
        ->and($admin->can('keuangan.voucher.create'))->toBeTrue()
        ->and($admin->can('keuangan.voucher.edit'))->toBeTrue()
        ->and($admin->can('keuangan.voucher.delete'))->toBeTrue()
        ->and($admin->can('portal.user.manage'))->toBeTrue();

    $resKeuangan = $this->actingAs($admin)->get('/keuangan');
    expect($resKeuangan->status())->toBe(200);

    $resSettings = $this->actingAs($admin)->get('/settings');
    expect($resSettings->status())->toBe(200);

    // Verify all settings sub-pages render without error
    $resUserCreate = $this->actingAs($admin)->get('/settings/users/create');
    expect($resUserCreate->status())->toBe(200);

    $resUserEdit = $this->actingAs($admin)->get("/settings/users/{$admin->id}/edit");
    expect($resUserEdit->status())->toBe(200);

    $resRoleCreate = $this->actingAs($admin)->get('/settings/roles/create');
    expect($resRoleCreate->status())->toBe(200);

    $roleBendahara = \Spatie\Permission\Models\Role::where('name', 'Bendahara Keuangan')->first();
    $resRoleEdit = $this->actingAs($admin)->get("/settings/roles/{$roleBendahara->id}/edit");
    expect($resRoleEdit->status())->toBe(200);

    $resLogs = $this->actingAs($admin)->get('/settings/activity-logs');
    expect($resLogs->status())->toBe(200);

    $resConfig = $this->actingAs($admin)->get('/settings/pengaturan-gereja');
    expect($resConfig->status())->toBe(200);
});

test('user a (operator) can access keuangan, post and view vouchers, but cannot edit, delete, or access settings', function () {
    $userA = User::where('email', 'operator@gpibhosiana.org')->first();

    // Module access checks
    expect($userA->canAccessModule('keuangan'))->toBeTrue()
        ->and($userA->canAccessModule('settings'))->toBeFalse();

    // Keuangan granular permissions
    expect($userA->can('keuangan.voucher.view'))->toBeTrue()
        ->and($userA->can('keuangan.voucher.create'))->toBeTrue()
        ->and($userA->can('keuangan.voucher.edit'))->toBeFalse()
        ->and($userA->can('keuangan.voucher.delete'))->toBeFalse()
        ->and($userA->can('keuangan.coa.manage'))->toBeFalse();

    // Policy gate checks
    $dummyVoucher = new Voucher([
        'no_bukti' => 'TEST-001',
        'jenis_voucher' => 'BKM',
        'tanggal' => '2026-08-24',
        'pihak_terkait' => 'Jemaat Test',
        'kode_akun' => '111.01',
    ]);

    expect(Gate::forUser($userA)->allows('viewAny', Voucher::class))->toBeTrue()
        ->and(Gate::forUser($userA)->allows('create', Voucher::class))->toBeTrue()
        ->and(Gate::forUser($userA)->allows('update', $dummyVoucher))->toBeFalse()
        ->and(Gate::forUser($userA)->allows('delete', $dummyVoucher))->toBeFalse();

    // Direct panel route access test
    $response = $this->actingAs($userA)->get('/settings');
    expect($response->status())->toBe(403);
});

test('user b (bendahara) can access keuangan and perform all CRUD operations, but cannot access settings', function () {
    $userB = User::where('email', 'bendahara@gpibhosiana.org')->first();

    // Module access checks
    expect($userB->canAccessModule('keuangan'))->toBeTrue()
        ->and($userB->canAccessModule('settings'))->toBeFalse();

    // Granular permissions
    expect($userB->can('keuangan.voucher.view'))->toBeTrue()
        ->and($userB->can('keuangan.voucher.create'))->toBeTrue()
        ->and($userB->can('keuangan.voucher.edit'))->toBeTrue()
        ->and($userB->can('keuangan.voucher.delete'))->toBeTrue()
        ->and($userB->can('keuangan.coa.manage'))->toBeTrue();

    // Policy gate checks
    $dummyVoucher = new Voucher([
        'no_bukti' => 'TEST-002',
        'jenis_voucher' => 'BKK',
        'tanggal' => '2026-08-24',
        'pihak_terkait' => 'Supplier Test',
        'kode_akun' => '111.01',
    ]);

    expect(Gate::forUser($userB)->allows('viewAny', Voucher::class))->toBeTrue()
        ->and(Gate::forUser($userB)->allows('create', Voucher::class))->toBeTrue()
        ->and(Gate::forUser($userB)->allows('update', $dummyVoucher))->toBeTrue()
        ->and(Gate::forUser($userB)->allows('delete', $dummyVoucher))->toBeTrue();

    // Direct panel route access test
    $response = $this->actingAs($userB)->get('/settings');
    expect($response->status())->toBe(403);
});

test('activity log records automatically when voucher is created and updated', function () {
    $userB = User::where('email', 'bendahara@gpibhosiana.org')->first();
    $this->actingAs($userB);

    $coa = ChartOfAccount::first();

    $voucher = Voucher::create([
        'no_bukti' => 'BKM-LOG-TEST',
        'jenis_voucher' => 'BKM',
        'tanggal' => '2026-08-24',
        'pihak_terkait' => 'Donatur Test Log',
        'kode_akun' => $coa ? $coa->kode_akun : '111.01',
    ]);

    $logCreate = ActivityLog::where('subject_type', Voucher::class)
        ->where('subject_id', 'BKM-LOG-TEST')
        ->latest('id')
        ->first();

    expect($logCreate)->not->toBeNull()
        ->and($logCreate->causer_id)->toBe($userB->id)
        ->and($logCreate->description)->toContain('Menambahkan Voucher')
        ->and($logCreate->log_name)->toBe('keuangan');

    // Update voucher
    $voucher->update([
        'pihak_terkait' => 'Donatur Test Log Updated',
    ]);

    $logUpdate = ActivityLog::where('subject_type', Voucher::class)
        ->where('subject_id', 'BKM-LOG-TEST')
        ->latest('id')
        ->first();

    expect($logUpdate)->not->toBeNull()
        ->and($logUpdate->description)->toContain('Mengubah Voucher')
        ->and($logUpdate->properties)->toHaveKey('old')
        ->and($logUpdate->properties['attributes']['pihak_terkait'])->toBe('Donatur Test Log Updated');
});

test('admin can create new user through UserResource with roles and password', function () {
    \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('settings'));

    $admin = User::where('email', 'admin@gpibhosiana.org')->first();
    $role = \Spatie\Permission\Models\Role::where('name', 'Operator Kasir')->first();

    \Livewire\Livewire::actingAs($admin)
        ->test(\App\Filament\Settings\Resources\UserResource\Pages\CreateUser::class)
        ->fillForm([
            'name' => 'Pendeta Baru',
            'email' => 'pendeta@gpibhosiana.org',
            'password' => 'SecurePass123!',
            'is_active' => true,
            'roles' => [$role->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $newUser = User::where('email', 'pendeta@gpibhosiana.org')->first();
    expect($newUser)->not->toBeNull()
        ->and($newUser->name)->toBe('Pendeta Baru')
        ->and($newUser->is_active)->toBeTrue()
        ->and($newUser->hasRole('Operator Kasir'))->toBeTrue()
        ->and(\Illuminate\Support\Facades\Hash::check('SecurePass123!', $newUser->password))->toBeTrue();
});

