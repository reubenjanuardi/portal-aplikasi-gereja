<?php

use App\Models\ChartOfAccount;
use App\Models\OpeningBalance;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Services\BukuBesarService;

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

    $this->bank = ChartOfAccount::create([
        'kode_akun'   => '112.02',
        'nama_akun'   => 'Bank BRI - Rekening Tabungan Operasional',
        'kategori'    => 'Kas & Bank',
        'is_postable' => true,
    ]);
});

/**
 * Buat satu voucher + satu baris transaksi.
 */
function buat(string $noBukti, string $tanggal, string $jenis, string $kasBank, string $mataAnggaran, float $nominal): void
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

function saldoKasKecil(string $start, string $end): ?array
{
    $data = app(BukuBesarService::class)->getReportData($start, $end)['accounts'];
    $row = $data->firstWhere('kode_akun', '111.02');

    return $row ? [
        'awal'  => (float) $row['saldo_awal'],
        'akhir' => (float) $row['saldo_akhir'],
    ] : null;
}

test('saldo awal dipakai sebagai titik awal ketika tidak ada transaksi backdate', function () {
    OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-06-30',
        'saldo_awal' => 5_000_000,
    ]);

    buat('BKM-JUL-01', '2026-07-02', 'BKM', '111.02', '362.01.01', 1_000_000);

    $hasil = saldoKasKecil('2026-07-01', '2026-07-08');
    expect($hasil['awal'])->toBe(5_000_000.0);
    expect($hasil['akhir'])->toBe(6_000_000.0);
});

test('MENGHAPUS saldo awal mengembalikan perhitungan ke mutasi saja', function () {
    $ob = OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-06-30',
        'saldo_awal' => 5_000_000,
    ]);

    buat('BKM-JUL-01', '2026-07-02', 'BKM', '111.02', '362.01.01', 1_000_000);

    // Sebelum dihapus: saldo awal 5 juta ikut terhitung.
    expect(saldoKasKecil('2026-07-01', '2026-07-08')['awal'])->toBe(5_000_000.0);

    // Hapus saldo awal.
    $ob->delete();
    expect(OpeningBalance::count())->toBe(0);

    // Sesudah dihapus: saldo awal kembali 0, hanya mutasi yang dihitung.
    $hasil = saldoKasKecil('2026-07-01', '2026-07-08');
    expect($hasil['awal'])->toBe(0.0);
    expect($hasil['akhir'])->toBe(1_000_000.0);
});

test('backdate transaksi Juni + hapus saldo awal = angka benar tanpa double count', function () {
    // Awalnya pakai rekap: saldo akhir 30 Juni = 5 juta.
    $ob = OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-06-30',
        'saldo_awal' => 5_000_000,
    ]);

    // Juni kemudian diperbaiki satu per satu sampai lengkap.
    buat('BKM-JUN-01', '2026-06-10', 'BKM', '111.02', '362.01.01', 3_000_000);
    buat('BKK-JUN-01', '2026-06-20', 'BKK', '111.02', '362.01.01', 1_000_000);

    // Mutasi Juni tidak dijumlahkan karena sudah tercakup saldo awal rekap.
    // Kalau tidak ada penjaga double count, hasilnya 7 juta (5 + 2).
    expect(saldoKasKecil('2026-07-01', '2026-07-08')['awal'])->toBe(5_000_000.0);

    // Hapus rekap, karena sekarang sudah ada data aslinya.
    $ob->delete();

    // Benar: saldo awal = hasil mutasi Juni saja = 2 juta.
    $hasil = saldoKasKecil('2026-07-01', '2026-07-08');
    expect($hasil['awal'])->toBe(2_000_000.0);
});

test('hapus bisa dilakukan per akun, tidak harus semua', function () {
    $kas = OpeningBalance::create([
        'kode_akun'  => '111.02',
        'periode'    => '2026-06-30',
        'saldo_awal' => 5_000_000,
    ]);

    $bank = OpeningBalance::create([
        'kode_akun'  => '112.02',
        'periode'    => '2026-06-30',
        'saldo_awal' => 15_000_000,
    ]);

    // Hanya data Kas Kecil yang sudah dibackdate; Bank masih pakai rekap.
    buat('BKM-JUN-01', '2026-06-10', 'BKM', '111.02', '362.01.01', 3_000_000);

    $kas->delete();

    expect(OpeningBalance::count())->toBe(1);

    // Kas Kecil memakai mutasi Juni, Bank memakai rekap. Perhitungan tetap
    // terpisah per akun sehingga menghapus saldo awal satu akun tidak
    // mengganggu akun lain.
    $data = app(BukuBesarService::class)->getReportData('2026-07-01', '2026-07-08')['accounts'];
    $kasKecil = $data->firstWhere('kode_akun', '111.02');
    $bank = $data->firstWhere('kode_akun', '112.02');

    expect($bank)->not->toBeNull();
    expect((float) $bank['saldo_awal'])->toBe(15_000_000.0);
});
