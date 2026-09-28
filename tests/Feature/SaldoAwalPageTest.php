<?php

use App\Models\ChartOfAccount;
use App\Models\OpeningBalance;
use App\Models\User;
use Livewire\Livewire;
use App\Filament\Pages\SaldoAwal;

test('halaman saldo awal butuh login', function () {
    $this->get('/keuangan/saldo-awal')->assertRedirect(route('login'));
});

test('pengguna dengan izin coa.manage bisa membuka halaman saldo awal', function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    $this->actingAs($user)->get('/keuangan/saldo-awal')->assertStatus(200);
});

test('simpan saldo awal lewat form', function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    ChartOfAccount::create([
        'kode_akun'   => '111.02',
        'nama_akun'   => 'Kas Kecil',
        'kategori'    => 'Kas & Bank',
        'is_postable' => true,
    ]);

    Livewire::actingAs($user)->test(SaldoAwal::class)
        ->fillForm([
            'periode'    => '2026-07-01',
            'kodeAkun'   => '111.02',
            'saldoAwal'  => '7.500.000',
            'keterangan' => 'Kas kecil awal Juli',
        ])
        ->call('saveAction')
        ->assertHasNoFormErrors();

    $row = OpeningBalance::where('kode_akun', '111.02')
        ->whereDate('periode', '2026-07-01')
        ->first();

    expect($row)->not->toBeNull();
    expect((float) $row->saldo_awal)->toBe(7_500_000.0);
});

test('simpan ulang akun + periode yang sama menimpa nilai lama', function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    ChartOfAccount::create([
        'kode_akun'   => '111.02',
        'nama_akun'   => 'Kas Kecil',
        'kategori'    => 'Kas & Bank',
        'is_postable' => true,
    ]);

    OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-07-01',
        'saldo_awal' => 1_000_000,
    ]);

    Livewire::actingAs($user)->test(SaldoAwal::class)
        ->fillForm([
            'periode'   => '2026-07-01',
            'kodeAkun'  => '111.02',
            'saldoAwal' => '2.250.000',
        ])
        ->call('saveAction')
        ->assertHasNoFormErrors();

    expect(OpeningBalance::count())->toBe(1);
    expect((float) OpeningBalance::first()->saldo_awal)->toBe(2_250_000.0);
});

test('hapus saldo awal', function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    ChartOfAccount::create([
        'kode_akun'   => '111.02',
        'nama_akun'   => 'Kas Kecil',
        'kategori'    => 'Kas & Bank',
        'is_postable' => true,
    ]);

    $row = OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-07-01',
        'saldo_awal' => 3_000_000,
    ]);

    Livewire::actingAs($user)->test(SaldoAwal::class)
        ->call('deleteAction', $row->id);

    expect(OpeningBalance::count())->toBe(0);
});
