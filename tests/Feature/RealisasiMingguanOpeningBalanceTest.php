<?php

use App\Models\ChartOfAccount;
use App\Models\OpeningBalance;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Services\BukuBesarService;
use App\Services\LaporanRealisasiService;

beforeEach(function () {
    $this->kasKecil = ChartOfAccount::create([
        'kode_akun'   => '111.02',
        'nama_akun'   => 'Kas Kecil',
        'kategori'    => 'Kas & Bank',
        'is_postable' => true,
    ]);

    $this->bank = ChartOfAccount::create([
        'kode_akun'   => '112.02',
        'nama_akun'   => 'Bank BRI - Rekening Tabungan Operasional',
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

function buatVoucherRealisasi(string $noBukti, string $tanggal, string $jenis, string $kasBank, string $mataAnggaran, float $nominal): void
{
    Voucher::create([
        'no_bukti'           => $noBukti,
        'tanggal'            => $tanggal,
        'pihak_terkait'      => 'Uji',
        'jenis_voucher'      => $jenis,
        'kode_akun_kas_bank' => $kasBank,
        'kode_akun'          => $mataAnggaran,
        'total_nominal'      => $nominal,
    ]);

    Transaction::create([
        'no_bukti'  => $noBukti,
        'kode_akun' => $mataAnggaran,
        'uraian'    => 'Uji',
        'nominal'   => $nominal,
    ]);
}

/**
 * Ambil baris Kas & Bank dari hasil Laporan Realisasi Mingguan.
 */
function barisRealisasi(array $report, string $kode): ?array
{
    foreach ($report['kasBank'] ?? [] as $item) {
        if ($item['kode_akun'] === $kode) {
            return $item;
        }
    }

    return null;
}

test('minggu 1-8 Juli memakai saldo awal rekap 30 Juni, bukan 0', function () {
    OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-06-30',
        'saldo_awal' => 5_000_000,
        'keterangan' => 'Rekap saldo kas kecil per 30 Juni 2026',
    ]);

    // Mutasi pada minggu 1-8 Juli
    buatVoucherRealisasi('BKM-JUL-01', '2026-07-02', 'BKM', '111.02', '362.01.01', 1_000_000);
    buatVoucherRealisasi('BKK-JUL-01', '2026-07-05', 'BKK', '111.02', '362.01.01', 500_000);

    $report = app(LaporanRealisasiService::class)->getWeeklyReport('2026-07-01', '2026-07-08');

    $kas = barisRealisasi($report, '111.02');
    expect($kas)->not->toBeNull();
    expect((float) $kas['saldo_awal'])->toBe(5_000_000.0);
    expect((float) $kas['saldo_akhir'])->toBe(5_500_000.0);
});

test('saldo awal tidak double count dengan mutasi Juni yang sudah tercatat', function () {
    // Rekap 30 Juni = 5 juta.
    OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-06-30',
        'saldo_awal' => 5_000_000,
    ]);

    // Sebagian data Juni sudah di-backdate (2 juta).
    buatVoucherRealisasi('BKM-JUN-01', '2026-06-10', 'BKM', '111.02', '362.01.01', 3_000_000);
    buatVoucherRealisasi('BKK-JUN-01', '2026-06-20', 'BKK', '111.02', '362.01.01', 1_000_000);

    buatVoucherRealisasi('BKM-JUL-01', '2026-07-02', 'BKM', '111.02', '362.01.01', 500_000);

    $report = app(LaporanRealisasiService::class)->getWeeklyReport('2026-07-01', '2026-07-08');
    $kas = barisRealisasi($report, '111.02');

    // Harusnya 5 juta (rekap), bukan 7 juta (rekap + mutasi Juni).
    expect((float) $kas['saldo_awal'])->toBe(5_000_000.0);
    expect((float) $kas['saldo_akhir'])->toBe(5_500_000.0);
});

test('Buku Besar dan Realisasi Mingguan memberi angka sama untuk 1 Juli', function () {
    OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-06-30',
        'saldo_awal' => 5_000_000,
    ]);

    buatVoucherRealisasi('BKM-JUL-01', '2026-07-02', 'BKM', '111.02', '362.01.01', 1_000_000);
    buatVoucherRealisasi('BKK-JUL-01', '2026-07-05', 'BKK', '111.02', '362.01.01', 500_000);

    $bukuBesar = app(BukuBesarService::class)->getReportData('2026-07-01', '2026-07-31')['accounts'];
    $bb = $bukuBesar->firstWhere('kode_akun', '111.02');

    $report = app(LaporanRealisasiService::class)->getWeeklyReport('2026-07-01', '2026-07-08');
    $rm = barisRealisasi($report, '111.02');

    expect((float) $bb['saldo_awal'])->toBe((float) $rm['saldo_awal']);
    // Saldo akhir keduanya dihitung dari data yang sama.
    expect((float) $bb['saldo_akhir'])->toBe((float) $rm['saldo_akhir']);
});

test('hapus saldo awal mengembalikan perhitungan ke mutasi saja', function () {
    $ob = OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-06-30',
        'saldo_awal' => 5_000_000,
    ]);

    buatVoucherRealisasi('BKM-JUN-01', '2026-06-10', 'BKM', '111.02', '362.01.01', 3_000_000);
    buatVoucherRealisasi('BKM-JUL-01', '2026-07-02', 'BKM', '111.02', '362.01.01', 500_000);

    $sebelum = barisRealisasi(app(LaporanRealisasiService::class)->getWeeklyReport('2026-07-01', '2026-07-08'), '111.02');
    expect((float) $sebelum['saldo_awal'])->toBe(5_000_000.0);

    $ob->delete();

    $sesudah = barisRealisasi(app(LaporanRealisasiService::class)->getWeeklyReport('2026-07-01', '2026-07-08'), '111.02');
    // Kembali ke mutasi Juni saja.
    expect((float) $sesudah['saldo_awal'])->toBe(3_000_000.0);
    expect((float) $sesudah['saldo_akhir'])->toBe(3_500_000.0);
});

test('tanpa saldo awal, mingguan tetap memakai mutasi seperti sebelumnya', function () {
    OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-06-30',
        'saldo_awal' => 5_000_000,
    ]);

    buatVoucherRealisasi('BKM-JUL-01', '2026-07-02', 'BKM', '111.02', '362.01.01', 1_000_000);

    // Laporan untuk minggu SEBELUM saldo awal: 24-30 Juni tidak punya saldo awal.
    $reportJuni = app(LaporanRealisasiService::class)->getWeeklyReport('2026-06-24', '2026-06-30');
    $kasJuni = barisRealisasi($reportJuni, '111.02');
    expect((float) ($kasJuni['saldo_awal'] ?? 0.0))->toBe(0.0);
});
