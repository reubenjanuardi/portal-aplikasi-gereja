<?php

use App\Models\ChartOfAccount;
use App\Models\OpeningBalance;
use App\Models\User;
use App\Filament\Pages\SaldoAwal;
use Livewire\Livewire;

test('tombol hapus di tabel menghapus saldo awal', function () {
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
        'periode'    => '2026-06-30',
        'saldo_awal' => 5_000_000,
    ]);

    Livewire::actingAs($user)->test(SaldoAwal::class)
        ->callTableAction('hapus', $row);

    expect(OpeningBalance::count())->toBe(0);
});

test('hapus lewat UI tidak menghapus baris lain', function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    foreach ([['111.02', 'Kas Kecil'], ['112.02', 'Bank BRI']] as [$kode, $nama]) {
        ChartOfAccount::create([
            'kode_akun'   => $kode,
            'nama_akun'   => $nama,
            'kategori'    => 'Kas & Bank',
            'is_postable' => true,
        ]);
    }

    $kas = OpeningBalance::create(['kode_akun' => '111.02', 'periode' => '2026-06-30', 'saldo_awal' => 5_000_000]);
    OpeningBalance::create(['kode_akun' => '112.02', 'periode' => '2026-06-30', 'saldo_awal' => 15_000_000]);

    Livewire::actingAs($user)->test(SaldoAwal::class)->callTableAction('hapus', $kas);

    expect(OpeningBalance::count())->toBe(1);
    expect(OpeningBalance::first()->kode_akun)->toBe('112.02');
});
