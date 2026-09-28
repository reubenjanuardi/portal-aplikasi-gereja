<?php

use App\Models\ChartOfAccount;
use App\Models\OpeningBalance;
use App\Models\User;
use App\Services\BukuBesarService;
use Livewire\Livewire;
use App\Filament\Pages\SaldoAwal;

beforeEach(function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $this->user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    foreach ([
        ['111.02', 'Kas Kecil'],
        ['112.02', 'Bank BRI - Rekening Tabungan Operasional'],
        ['112.03', 'Bank BRI - Rekening Tabungan PEG'],
        ['112.04', 'Bank DKI - Rekening Tabungan BOTI'],
        ['115.01', 'Deposito Bank BRI'],
        ['115.02', 'Deposito Bank 02'],
        ['362.01.01', 'Beban Listrik PLN'],
    ] as [$kode, $nama]) {
        ChartOfAccount::create([
            'kode_akun'   => $kode,
            'nama_akun'   => $nama,
            'kategori'    => $kode === '362.01.01' ? 'Pengeluaran' : 'Kas & Bank',
            'is_postable' => true,
        ]);
    }
});

test('dropdown hanya menampilkan akun kas & bank secara default', function () {
    $component = Livewire::actingAs($this->user)->test(SaldoAwal::class);

    $options = (fn () => $this->coaOptions())->call($component->instance());

    expect($options)->toHaveCount(6);
    expect(array_keys($options))->toContain('111.02');
    expect(array_keys($options))->toContain('115.01');
});

test('mematikan sakelar menampilkan seluruh akun postable', function () {
    ChartOfAccount::create([
        'kode_akun'   => '21.01.01',
        'nama_akun'   => 'Kolekte Ibadah',
        'kategori'    => 'Penerimaan',
        'is_postable' => true,
    ]);

    $component = Livewire::actingAs($this->user)->test(SaldoAwal::class)
        ->set('hanyaKasBank', false);

    $options = (fn () => $this->coaOptions())->call($component->instance());

    expect($options)->toHaveCount(8);
    expect(array_keys($options))->toContain('21.01.01');
});

test('saldo awal 5 akun kas & bank langsung dipakai buku besar', function () {
    foreach ([
        '111.02' => 2_500_000,
        '112.02' => 15_000_000,
        '112.03' => 3_000_000,
        '112.04' => 7_500_000,
        '115.01' => 25_000_000,
    ] as $kode => $nominal) {
        OpeningBalance::create([
            'kode_akun'  => $kode,
            'periode'    => '2026-07-01',
            'saldo_awal' => $nominal,
            'keterangan' => 'Saldo akhir 30 Juni 2026',
        ]);
    }

    // Transaksi setelah 1 Juli pada Kas Kecil: keluar 500 ribu
    \App\Models\Voucher::create([
        'no_bukti'           => 'BKK-JUL-01',
        'tanggal'            => '2026-07-10',
        'pihak_terkait'      => 'PLN',
        'jenis_voucher'      => 'BKK',
        'kode_akun_kas_bank' => '111.02',
        'kode_akun'          => '362.01.01',
        'total_nominal'      => 500_000,
    ]);
    \App\Models\Transaction::create([
        'no_bukti'  => 'BKK-JUL-01',
        'kode_akun' => '362.01.01',
        'uraian'    => 'Listrik',
        'nominal'   => 500_000,
    ]);

    $data = app(BukuBesarService::class)->getReportData('2026-07-01', '2026-07-31')['accounts'];

    $kasKecil = $data->firstWhere('kode_akun', '111.02');
    expect((float) $kasKecil['saldo_awal'])->toBe(2_500_000.0);
    expect((float) $kasKecil['saldo_akhir'])->toBe(2_000_000.0);

    $bank = $data->firstWhere('kode_akun', '112.02');
    expect((float) $bank['saldo_akhir'])->toBe(15_000_000.0);

    $deposito = $data->firstWhere('kode_akun', '115.01');
    expect((float) $deposito['saldo_akhir'])->toBe(25_000_000.0);

    // Total kas & bank = seluruh saldo awal
    $total = (float) $data->where('is_kas_bank', true)->sum('saldo_akhir');
    expect($total)->toBe(2_000_000.0 + 15_000_000.0 + 3_000_000.0 + 7_500_000.0 + 25_000_000.0);
});

test('akun tanpa mutasi tetap tampil kalau punya saldo awal', function () {
    OpeningBalance::create([
        'kode_akun'  => '112.04',
        'periode'    => '2026-07-01',
        'saldo_awal' => 7_500_000,
    ]);

    $data = app(BukuBesarService::class)->getReportData('2026-07-01', '2026-07-31')['accounts'];

    $boti = $data->firstWhere('kode_akun', '112.04');
    expect($boti)->not->toBeNull();
    expect((float) $boti['saldo_akhir'])->toBe(7_500_000.0);
    expect($boti['lines'])->toHaveCount(0);
});
