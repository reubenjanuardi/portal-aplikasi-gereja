<?php

use App\Models\ChartOfAccount;
use App\Models\OpeningBalance;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Services\BukuBesarService;
use App\Services\LaporanRealisasiService;

beforeEach(function () {
    foreach ([
        ['111.02', 'Kas Kecil'],
        ['112.02', 'Bank BRI Operasional'],
        ['362.01.01', 'Beban Listrik'],
        ['21.01.01', 'Kolekte Ibadah'],
    ] as [$kode, $nama]) {
        ChartOfAccount::create([
            'kode_akun'   => $kode,
            'nama_akun'   => $nama,
            'kategori'    => str_starts_with($kode, '3') ? 'Pengeluaran' : (str_starts_with($kode, '21') ? 'Penerimaan' : 'Kas & Bank'),
            'is_postable' => true,
        ]);
    }
});

/**
 * Satu voucher dengan akun sumber (kode_akun_kas_bank) dan akun tujuan
 * (kode_akun). Untuk transfer antar kas/bank, dua sisi ini dihitung dari
 * satu baris voucher yang sama.
 */
function buatVoucherX(string $no, string $tanggal, string $jenis, string $sumber, string $tujuan, float $nominal): void
{
    Voucher::create([
        'no_bukti' => $no, 'tanggal' => $tanggal, 'pihak_terkait' => 'Uji',
        'jenis_voucher' => $jenis, 'kode_akun_kas_bank' => $sumber,
        'kode_akun' => $tujuan, 'total_nominal' => $nominal,
    ]);

    Transaction::create([
        'no_bukti' => $no, 'kode_akun' => $tujuan,
        'uraian' => 'Uji', 'nominal' => $nominal,
    ]);
}

/**
 * @return array{awal: float, debit: float, kredit: float, akhir: float}
 */
function saldoX(string $kode, string $mulai, string $akhir): array
{
    $rows = app(BukuBesarService::class)->getReportData($mulai, $akhir)['accounts'];
    $r = $rows->firstWhere('kode_akun', $kode);

    return [
        'awal'   => (float) ($r['saldo_awal'] ?? 0),
        'debit'  => (float) ($r['total_debit'] ?? 0),
        'kredit' => (float) ($r['total_kredit'] ?? 0),
        'akhir'  => (float) ($r['saldo_akhir'] ?? 0),
    ];
}

test('tarik tunai bank ke kas cukup SATU voucher BBK', function () {
    OpeningBalance::create(['kode_akun' => '111.02', 'periode' => '2026-06-30', 'saldo_awal' => 100_000]);
    OpeningBalance::create(['kode_akun' => '112.02', 'periode' => '2026-06-30', 'saldo_awal' => 10_000_000]);

    // Bank BRI (112.02) keluar 2.500.000 ke Kas Kecil (111.02)
    buatVoucherX('BBK-001', '2026-07-01', 'BBK', '112.02', '111.02', 2_500_000);

    $kas = saldoX('111.02', '2026-07-01', '2026-07-08');
    $bank = saldoX('112.02', '2026-07-01', '2026-07-08');

    expect($kas['debit'])->toBe(2_500_000.0);
    expect($kas['akhir'])->toBe(2_600_000.0);

    expect($bank['kredit'])->toBe(2_500_000.0);
    expect($bank['akhir'])->toBe(7_500_000.0);
});

test('ringkasan Masuk/Keluar sama dengan kolom Debet/Kredit untuk akun kas', function () {
    OpeningBalance::create(['kode_akun' => '111.02', 'periode' => '2026-06-30', 'saldo_awal' => 102_087]);
    OpeningBalance::create(['kode_akun' => '112.02', 'periode' => '2026-06-30', 'saldo_awal' => 14_364_337]);

    // Tarik tunai 2.500.000 dengan BBK: untuk Kas Kecil ini adalah DEBET
    // walau jenis vouchernya "keluar".
    buatVoucherX('BBK-101', '2026-07-01', 'BBK', '112.02', '111.02', 2_500_000);

    // Transaksi kas biasa: masuk 570.000 dan keluar 65.700
    buatVoucherX('BKM-101', '2026-07-03', 'BKM', '111.02', '21.01.01', 570_000);
    buatVoucherX('BKK-101', '2026-07-03', 'BKK', '111.02', '362.01.01', 65_700);

    $r = app(BukuBesarService::class)
        ->getReportData('2026-07-01', '2026-07-08')['accounts']
        ->firstWhere('kode_akun', '111.02');

    // Ringkasan harus mengikuti kolom debit/kredit, bukan jenis voucher.
    expect((float) $r['total_masuk'])->toBe((float) $r['total_debit']);
    expect((float) $r['total_keluar'])->toBe((float) $r['total_kredit']);

    expect((float) $r['total_masuk'])->toBe(3_070_000.0);
    expect((float) $r['total_keluar'])->toBe(65_700.0);
    expect((float) $r['saldo_akhir'])->toBe((float) (102_087 + 3_070_000 - 65_700));
});

test('setor kas kecil ke bank cukup SATU voucher BKM', function () {
    OpeningBalance::create(['kode_akun' => '111.02', 'periode' => '2026-06-30', 'saldo_awal' => 10_000_000]);
    OpeningBalance::create(['kode_akun' => '112.02', 'periode' => '2026-06-30', 'saldo_awal' => 1_000_000]);

    // Kas Kecil (111.02) setor 5.030.000 ke Bank BRI (112.02).
    // BKM: sumber 111.02 (kas), tujuan 112.02 (bank).
    buatVoucherX('BKM-001', '2026-07-08', 'BKM', '111.02', '112.02', 5_030_000);

    $kas = saldoX('111.02', '2026-07-01', '2026-07-08');
    $bank = saldoX('112.02', '2026-07-01', '2026-07-08');

    // Pada BKM, akun sumber (kas) bertambah dan akun tujuan (bank) berkurang
    // dari sisi transfer, jadi saldo akhir tetap konservatif.
    expect($kas['akhir'])->toBe(15_030_000.0);
    expect($bank['akhir'])->toBe(-4_030_000.0);
});

test('total Kas & Bank tidak berubah oleh transfer internal', function () {
    OpeningBalance::create(['kode_akun' => '111.02', 'periode' => '2026-06-30', 'saldo_awal' => 10_000_000]);
    OpeningBalance::create(['kode_akun' => '112.02', 'periode' => '2026-06-30', 'saldo_awal' => 20_000_000]);

    buatVoucherX('BBK-002', '2026-07-02', 'BBK', '112.02', '111.02', 3_000_000);

    $kas = saldoX('111.02', '2026-07-01', '2026-07-08')['akhir'];
    $bank = saldoX('112.02', '2026-07-01', '2026-07-08')['akhir'];

    // Uang hanya pindah tempat, total harus tetap 30 juta.
    expect($kas + $bank)->toBe(30_000_000.0);
});

test('transaksi biasa dari luar tidak terpengaruh logika transfer', function () {
    OpeningBalance::create(['kode_akun' => '111.02', 'periode' => '2026-06-30', 'saldo_awal' => 100_000]);

    // Kolekte dari luar masuk ke kas kecil: sumber = kas kecil, tujuan = pos penerimaan.
    buatVoucherX('BKM-002', '2026-07-03', 'BKM', '111.02', '21.01.01', 570_000);

    $kas = saldoX('111.02', '2026-07-01', '2026-07-08');
    expect($kas['debit'])->toBe(570_000.0);
    expect($kas['akhir'])->toBe(670_000.0);
});

test('Buku Besar dan Realisasi konsisten untuk transfer tarik tunai', function () {
    OpeningBalance::create(['kode_akun' => '111.02', 'periode' => '2026-06-30', 'saldo_awal' => 102_087]);
    OpeningBalance::create(['kode_akun' => '112.02', 'periode' => '2026-06-30', 'saldo_awal' => 14_364_337]);

    buatVoucherX('BBK-003', '2026-07-01', 'BBK', '112.02', '111.02', 2_500_000);
    buatVoucherX('BBK-004', '2026-07-08', 'BBK', '112.02', '111.02', 2_750_000);

    $bb = app(BukuBesarService::class)->getReportData('2026-07-01', '2026-07-08')['accounts'];

    $rm = null;
    foreach (app(LaporanRealisasiService::class)->getWeeklyReport('2026-07-01', '2026-07-08')['kasBank'] as $i) {
        if ($i['kode_akun'] === '111.02') { $rm = $i; }
    }

    expect((float) $bb->firstWhere('kode_akun', '111.02')['saldo_akhir'])->toBe((float) $rm['saldo_akhir']);
});
