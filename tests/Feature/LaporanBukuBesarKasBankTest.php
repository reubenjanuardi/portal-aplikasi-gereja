<?php

use App\Filament\Pages\LaporanBukuBesar;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use App\Services\BukuBesarService;
use Livewire\Livewire;

beforeEach(function () {
    // Akun kas & bank
    $this->kasBesar = ChartOfAccount::create([
        'kode_akun'   => '111.01',
        'nama_akun'   => 'Kas Besar',
        'kategori'    => 'Kas & Bank',
        'is_postable' => true,
    ]);

    $this->kasKecil = ChartOfAccount::create([
        'kode_akun'   => '111.02',
        'nama_akun'   => 'Kas Kecil',
        'kategori'    => 'Kas & Bank',
        'is_postable' => true,
    ]);

    // Akun mata anggaran
    $this->listrik = ChartOfAccount::create([
        'kode_akun'   => '362.01.01',
        'nama_akun'   => 'Beban Listrik PLN',
        'kategori'    => 'Pengeluaran',
        'is_postable' => true,
    ]);

    $this->kolekte = ChartOfAccount::create([
        'kode_akun'   => '21.01.01',
        'nama_akun'   => 'Kolekte Ibadah Minggu Pagi',
        'kategori'    => 'Penerimaan',
        'is_postable' => true,
    ]);
});

/**
 * Bantu membuat voucher + satu baris transaksi.
 */
function buatVoucher(string $noBukti, string $jenis, string $tanggal, string $kasBank, string $mataAnggaran, float $nominal, string $uraian): void
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
        'uraian'    => $uraian,
        'nominal'   => $nominal,
    ]);
}

test('saldo Kas Kecil muncul di buku besar walaupun tidak pernah dipakai sebagai mata anggaran', function () {
    $periode = now()->startOfMonth()->toDateString();

    // Masuk Rp 1.000.000 ke Kas Kecil
    buatVoucher('BKM-T1-01', 'BKM', $periode, '111.02', '21.01.01', 1_000_000, 'Kolekte Via Kas Kecil');

    // Keluar Rp 250.000 dari Kas Kecil
    buatVoucher('BKK-T1-01', 'BKK', $periode, '111.02', '362.01.01', 250_000, 'Bayar Listrik via Kas Kecil');

    // Transaksi tidak pernah memakai kode_akun = 111.02
    expect(Transaction::where('kode_akun', '111.02')->count())->toBe(0);

    $data = app(BukuBesarService::class)->getReportData(
        startDate: $periode,
        endDate: now()->endOfMonth()->toDateString(),
    )['accounts'];

    $kasKecil = $data->firstWhere('kode_akun', '111.02');

    expect($kasKecil)->not->toBeNull();
    expect($kasKecil['is_kas_bank'])->toBeTrue();
    // Kas & Bank: penerimaan = Debet, pengeluaran = Kredit
    expect((float) $kasKecil['total_debit'])->toBe(1_000_000.0);
    expect((float) $kasKecil['total_kredit'])->toBe(250_000.0);
    expect((float) $kasKecil['total_masuk'])->toBe(1_000_000.0);
    expect((float) $kasKecil['total_keluar'])->toBe(250_000.0);
    expect((float) $kasKecil['saldo_akhir'])->toBe(750_000.0);
    expect($kasKecil['lines'])->toHaveCount(2);
});

test('mata anggaran tetap memakai arah debit/kredit yang berlawanan dengan kas', function () {
    $periode = now()->startOfMonth()->toDateString();

    buatVoucher('BKM-T2-01', 'BKM', $periode, '111.02', '21.01.01', 1_000_000, 'Kolekte');
    buatVoucher('BKK-T2-01', 'BKK', $periode, '111.02', '362.01.01', 250_000, 'Listrik');

    $data = app(BukuBesarService::class)->getReportData(
        startDate: $periode,
        endDate: now()->endOfMonth()->toDateString(),
    )['accounts'];

    // Kas Kecil: penerimaan = Debet, pengeluaran = Kredit
    $kasKecil = $data->firstWhere('kode_akun', '111.02');
    $kasMasuk = $kasKecil['lines']->firstWhere('no_bukti', 'BKM-T2-01');
    $kasKeluar = $kasKecil['lines']->firstWhere('no_bukti', 'BKK-T2-01');

    expect((float) $kasMasuk['debit'])->toBe(1_000_000.0);
    expect((float) $kasMasuk['kredit'])->toBe(0.0);
    expect((float) $kasKeluar['kredit'])->toBe(250_000.0);
    expect((float) $kasKeluar['debit'])->toBe(0.0);

    // Beban Listrik: pengeluaran = Debet
    $listrik = $data->firstWhere('kode_akun', '362.01.01');
    $listrikLine = $listrik['lines']->firstWhere('no_bukti', 'BKK-T2-01');
    expect((float) $listrikLine['debit'])->toBe(250_000.0);
    expect((float) $listrikLine['kredit'])->toBe(0.0);
    // Pos Pengeluaran bersaldo normal Debet
    expect((float) $listrik['total_debit'])->toBe(250_000.0);
    expect((float) $listrik['saldo_akhir'])->toBe(250_000.0);

    // Kolekte: penerimaan = Kredit
    $kolekte = $data->firstWhere('kode_akun', '21.01.01');
    $kolekteLine = $kolekte['lines']->firstWhere('no_bukti', 'BKM-T2-01');
    expect((float) $kolekteLine['kredit'])->toBe(1_000_000.0);
    expect((float) $kolekteLine['debit'])->toBe(0.0);
    // Akun Penerimaan bersaldo normal Kredit
    expect((float) $kolekte['total_kredit'])->toBe(1_000_000.0);
    expect((float) $kolekte['saldo_akhir'])->toBe(-1_000_000.0);
});

test('saldo awal periode ikut terhitung dari mutasi sebelum periode berjalan', function () {
    $bulanLalu = now()->subMonth()->startOfMonth()->toDateString();

    buatVoucher('BKM-T3-LALU', 'BKM', $bulanLalu, '111.02', '21.01.01', 500_000, 'Kolekte bulan lalu');

    $data = app(BukuBesarService::class)->getReportData(
        startDate: now()->startOfMonth()->toDateString(),
        endDate: now()->endOfMonth()->toDateString(),
    )['accounts'];

    $kasKecil = $data->firstWhere('kode_akun', '111.02');
    // Tidak ada mutasi bulan ini, jadi akun tidak ditampilkan
    expect($kasKecil)->toBeNull();

    // Tapi ketika ditransaksikan bulan ini, saldo awal terbawa
    buatVoucher('BKK-T3-INI', 'BKK', now()->startOfMonth()->toDateString(), '111.02', '362.01.01', 100_000, 'Listrik');

    $data2 = app(BukuBesarService::class)->getReportData(
        startDate: now()->startOfMonth()->toDateString(),
        endDate: now()->endOfMonth()->toDateString(),
    )['accounts'];

    $kasKecil2 = $data2->firstWhere('kode_akun', '111.02');
    expect((float) $kasKecil2['saldo_awal'])->toBe(500_000.0);
    expect((float) $kasKecil2['saldo_akhir'])->toBe(400_000.0);
});

test('filter kode akun Kas Kecil pada halaman buku besar mengembalikan saldo yang benar', function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    $periode = now()->startOfMonth()->toDateString();
    buatVoucher('BKM-003', 'BKM', $periode, '111.02', '21.01.01', 2_000_000, 'Kolekte');
    buatVoucher('BKK-003', 'BKK', $periode, '111.02', '362.01.01', 500_000, 'Listrik');
    buatVoucher('BKK-004', 'BKK', $periode, '111.01', '362.01.01', 750_000, 'Listrik dari Kas Besar');

    $component = Livewire::actingAs($user)->test(LaporanBukuBesar::class)
        ->set('startDate', $periode)
        ->set('endDate', now()->endOfMonth()->toDateString())
        ->set('kodeAkun', '111.02');

    $data = $component->get('reportData');

    expect($data)->toHaveCount(1);
    expect($data->first()['kode_akun'])->toBe('111.02');
    expect((float) $data->first()['saldo_akhir'])->toBe(1_500_000.0);

    // Total saldo kas & bank juga mengikuti filter
    expect((float) $component->get('totalSaldoKasBank'))->toBe(1_500_000.0);
});
