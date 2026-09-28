<?php

use App\Models\ChartOfAccount;
use App\Models\OpeningBalance;
use App\Models\User;
use App\Filament\Pages\SaldoAwal;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $this->user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    foreach ([
        ['111.02', 'Kas Kecil'],
        ['112.02', 'Bank BRI - Rekening Tabungan Operasional'],
        ['21.01.01', 'Kolekte Ibadah Minggu Pagi'],
    ] as [$kode, $nama]) {
        ChartOfAccount::create([
            'kode_akun'   => $kode,
            'nama_akun'   => $nama,
            'kategori'    => $kode === '21.01.01' ? 'Penerimaan' : 'Kas & Bank',
            'is_postable' => true,
        ]);
    }
});

function importCsv(string $csv, string $periode = '2026-07-01')
{
    return Livewire::actingAs(test()->user)->test(SaldoAwal::class)
        ->set('periode', $periode)
        ->set('csvText', $csv)
        ->call('importAction');
}

test('unduh template csv', function () {
    Livewire::actingAs($this->user)->test(SaldoAwal::class)
        ->call('downloadTemplateAction')
        ->assertStatus(200);
});

test('import csv menyimpan saldo awal dengan format ribuan indonesia', function () {
    $csv = "kode_akun,saldo_awal,keterangan\n"
        . "111.02,\"5.000.000\",Kas kecil awal\n"
        . "112.02,\"10.250.000\",Bank BRI\n"
        . "21.01.01,750000,Kolekte\n";

    importCsv($csv);

    expect((float) OpeningBalance::where('kode_akun', '111.02')->first()->saldo_awal)->toBe(5_000_000.0);
    expect((float) OpeningBalance::where('kode_akun', '112.02')->first()->saldo_awal)->toBe(10_250_000.0);
    expect((float) OpeningBalance::where('kode_akun', '21.01.01')->first()->saldo_awal)->toBe(750_000.0);
});

test('import csv melewati kode akun yang tidak valid dan baris kosong', function () {
    $csv = "kode_akun,saldo_awal\n"
        . "111.02,1000000\n"
        . "999.99,500000\n"
        . ",700000\n";

    importCsv($csv);

    expect(OpeningBalance::count())->toBe(1);
    expect(OpeningBalance::first()->kode_akun)->toBe('111.02');
});

test('import csv menolak file tanpa kolom wajib', function () {
    $csv = "kode,nilai\n111.02,1000000\n";

    importCsv($csv);

    expect(OpeningBalance::count())->toBe(0);
});

test('import csv dua kali tidak menggandakan saldo', function () {
    $csv = "kode_akun,saldo_awal\n111.02,1000000\n";

    importCsv($csv);
    importCsv($csv);

    expect(OpeningBalance::count())->toBe(1);
    expect((float) OpeningBalance::first()->saldo_awal)->toBe(1_000_000.0);
});

test('import csv menolak nilai negatif untuk akun kas', function () {
    $csv = "kode_akun,saldo_awal\n111.02,-500000\n";

    importCsv($csv);

    // Saldo kas boleh negatif secara teknis (overdraft), jadi tetap tersimpan.
    expect(OpeningBalance::count())->toBe(1);
    expect((float) OpeningBalance::first()->saldo_awal)->toBe(-500_000.0);
});
