<?php

use App\Models\ChartOfAccount;
use App\Models\OpeningBalance;
use App\Services\BukuBesarService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->kasKecil = ChartOfAccount::create([
        'kode_akun'   => '111.02',
        'nama_akun'   => 'Kas Kecil',
        'kategori'    => 'Kas & Bank',
        'is_postable' => true,
    ]);

    $this->listrik = ChartOfAccount::create([
        'kode_akun'   => '362.01.01',
        'nama_akun'   => 'Beban Listrik PLN',
        'kategori'    => 'Pengeluaran',
        'is_postable' => true,
    ]);
});

test('opening balance table starts empty and can be created', function () {
    expect(OpeningBalance::count())->toBe(0);

    OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-07-01',
        'saldo_awal' => 5_000_000,
    ]);

    expect(OpeningBalance::count())->toBe(1);
    expect(OpeningBalance::mapForPeriod('2026-07-01'))->toBe(['111.02' => 5_000_000.0]);
    expect(OpeningBalance::existingPeriods())->toBe(['2026-07-01']);
});

test('satu akun tidak bisa punya dua baris untuk periode yang sama', function () {
    OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-07-01',
        'saldo_awal' => 5_000_000,
    ]);

    expect(fn () => OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-07-01',
        'saldo_awal' => 9_000_000,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('saldo awal manual ditambahkan ke buku besar sebelum mutasi periode', function () {
    OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-07-01',
        'saldo_awal' => 5_000_000,
        'keterangan' => 'Saldo kas kecil per 1 Juli 2026',
    ]);

    // Transaksi setelah 1 Juli
    \App\Models\Voucher::create([
        'no_bukti'           => 'BKK-TEST-01',
        'tanggal'            => '2026-07-05',
        'pihak_terkait'      => 'PLN',
        'jenis_voucher'      => 'BKK',
        'kode_akun_kas_bank' => '111.02',
        'kode_akun'          => '362.01.01',
        'total_nominal'      => 500_000,
    ]);

    \App\Models\Transaction::create([
        'no_bukti'  => 'BKK-TEST-01',
        'kode_akun' => '362.01.01',
        'uraian'    => 'Bayar listrik',
        'nominal'   => 500_000,
    ]);

    $data = app(BukuBesarService::class)->getReportData('2026-07-01', '2026-07-31')['accounts'];

    $kasKecil = $data->firstWhere('kode_akun', '111.02');

    expect($kasKecil)->not->toBeNull();
    expect((float) $kasKecil['saldo_awal'])->toBe(5_000_000.0);
    expect((float) $kasKecil['total_kredit'])->toBe(500_000.0);
    expect((float) $kasKecil['saldo_akhir'])->toBe(4_500_000.0);
});

test('saldo awal tidak dihitung dua kali ketika ada mutasi transaksi setelah tanggal saldo awal', function () {
    // Saldo awal 1 Juli dicatat, dan ada mutasi SETELAH 1 Juli.
    OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-07-01',
        'saldo_awal' => 1_000_000,
    ]);

    \App\Models\Voucher::create([
        'no_bukti'           => 'BKM-TEST-01',
        'tanggal'            => '2026-07-10',
        'pihak_terkait'      => 'Jemaat',
        'jenis_voucher'      => 'BKM',
        'kode_akun_kas_bank' => '111.02',
        'kode_akun'          => '362.01.01',
        'total_nominal'      => 200_000,
    ]);

    \App\Models\Transaction::create([
        'no_bukti'  => 'BKM-TEST-01',
        'kode_akun' => '362.01.01',
        'uraian'    => 'Penerimaan',
        'nominal'   => 200_000,
    ]);

    // Laporan untuk periode SEBELUM tanggal saldo awal: saldo awal tidak boleh muncul.
    $dataSebelum = app(BukuBesarService::class)->getReportData('2026-06-01', '2026-06-30')['accounts'];
    $kasSebelum = $dataSebelum->firstWhere('kode_akun', '111.02');
    expect($kasSebelum)->toBeNull();

    // Laporan untuk periode setelah: saldo awal = 1 juta, mutasi = +200 ribu.
    $data = app(BukuBesarService::class)->getReportData('2026-07-01', '2026-07-31')['accounts'];
    $kasKecil = $data->firstWhere('kode_akun', '111.02');
    expect((float) $kasKecil['saldo_akhir'])->toBe(1_200_000.0);
});
