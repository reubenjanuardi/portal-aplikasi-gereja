<?php

use App\Filament\Pages\LaporanJurnalUmum;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use Livewire\Livewire;

beforeEach(function () {
    $this->kasBesar = ChartOfAccount::create([
        'kode_akun'   => '111.01',
        'nama_akun'   => 'Kas Besar',
        'kategori'    => 'Kas & Bank',
        'is_postable' => true,
    ]);

    $this->bankBri = ChartOfAccount::create([
        'kode_akun'   => '112.02',
        'nama_akun'   => 'Bank BRI - Rekening Operasional',
        'kategori'    => 'Kas & Bank',
        'is_postable' => true,
    ]);

    $this->kolekte = ChartOfAccount::create([
        'kode_akun'   => '21.01.01',
        'nama_akun'   => 'Kolekte Ibadah Minggu',
        'kategori'    => 'Penerimaan',
        'is_postable' => true,
    ]);

    $this->listrik = ChartOfAccount::create([
        'kode_akun'   => '362.01.01',
        'nama_akun'   => 'Beban Listrik PLN',
        'kategori'    => 'Pengeluaran',
        'is_postable' => true,
    ]);
});

test('guest cannot access Jurnal Umum page or exports', function () {
    $this->get('/keuangan/laporan-jurnal-umum')->assertRedirect();
    $this->get(route('laporan.jurnal-umum.pdf'))->assertRedirect(route('login'));
    $this->get(route('laporan.jurnal-umum.excel'))->assertRedirect(route('login'));
});

test('authenticated user with permission can view Jurnal Umum page', function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    $this->actingAs($user)->get('/keuangan/laporan-jurnal-umum')->assertStatus(200);
});

test('LaporanJurnalUmum correctly pairs debet and kredit entries for BKK and BKM and stays balanced', function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    // 1. Voucher BKK: Bayar Listrik via Kas Besar
    $voucherBkk = Voucher::create([
        'no_bukti'           => 'BKK-001',
        'tanggal'            => now()->toDateString(),
        'pihak_terkait'      => 'PT PLN Persero',
        'jenis_voucher'      => 'BKK',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun'          => $this->listrik->kode_akun,
        'total_nominal'      => 350000,
    ]);

    Transaction::create([
        'no_bukti'  => $voucherBkk->no_bukti,
        'kode_akun' => $voucherBkk->kode_akun,
        'uraian'    => 'Pembayaran tagihan listrik gereja',
        'nominal'   => 350000,
    ]);

    // 2. Voucher BKM: Terima Kolekte via Bank BRI
    $voucherBkm = Voucher::create([
        'no_bukti'           => 'BKM-001',
        'tanggal'            => now()->toDateString(),
        'pihak_terkait'      => 'Jemaat Sektor 2',
        'jenis_voucher'      => 'BKM',
        'kode_akun_kas_bank' => $this->bankBri->kode_akun,
        'kode_akun'          => $this->kolekte->kode_akun,
        'total_nominal'      => 650000,
    ]);

    Transaction::create([
        'no_bukti'  => $voucherBkm->no_bukti,
        'kode_akun' => $voucherBkm->kode_akun,
        'uraian'    => 'Penerimaan kolekte ibadah via transfer BRI',
        'nominal'   => 650000,
    ]);

    // Mount Livewire component
    $component = Livewire::actingAs($user)->test(LaporanJurnalUmum::class);

    $entries = $component->get('journalEntries');
    expect($entries)->toHaveCount(2);

    // Entry 1 (BKK): Debet = Beban Listrik (362.01.01), Kredit = Kas Besar (111.01)
    $bkkEntry = $entries->firstWhere('no_bukti', 'BKK-001');
    expect($bkkEntry)->not->toBeNull();
    expect($bkkEntry['debit']['kode_akun'])->toBe('362.01.01');
    expect($bkkEntry['debit']['nominal'])->toBe(350000.0);
    expect($bkkEntry['kredit']['kode_akun'])->toBe('111.01');
    expect($bkkEntry['kredit']['nominal'])->toBe(350000.0);

    // Entry 2 (BKM): Debet = Bank BRI (112.02), Kredit = Kolekte (21.01.01)
    $bkmEntry = $entries->firstWhere('no_bukti', 'BKM-001');
    expect($bkmEntry)->not->toBeNull();
    expect($bkmEntry['debit']['kode_akun'])->toBe('112.02');
    expect($bkmEntry['debit']['nominal'])->toBe(650000.0);
    expect($bkmEntry['kredit']['kode_akun'])->toBe('21.01.01');
    expect($bkmEntry['kredit']['nominal'])->toBe(650000.0);

    // Total Debit & Kredit must be identical (1,000,000) and Balanced
    expect($component->get('totalDebit'))->toBe(1000000.0);
    expect($component->get('totalKredit'))->toBe(1000000.0);
    expect($component->get('isBalanced'))->toBeTrue();
});

test('authenticated user can stream PDF and download Excel for Jurnal Umum', function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    $voucher = Voucher::create([
        'no_bukti'           => 'BKK-EXP-001',
        'tanggal'            => now()->toDateString(),
        'pihak_terkait'      => 'Toko ATK',
        'jenis_voucher'      => 'BKK',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun'          => $this->listrik->kode_akun,
        'total_nominal'      => 100000,
    ]);

    Transaction::create([
        'no_bukti'  => $voucher->no_bukti,
        'kode_akun' => $voucher->kode_akun,
        'uraian'    => 'Biaya operasional',
        'nominal'   => 100000,
    ]);

    // Test PDF
    $pdfResponse = $this->actingAs($user)->get(route('laporan.jurnal-umum.pdf'));
    $pdfResponse->assertStatus(200);
    $pdfResponse->assertHeader('content-type', 'application/pdf');

    // Test Excel
    $excelResponse = $this->actingAs($user)->get(route('laporan.jurnal-umum.excel'));
    $excelResponse->assertStatus(200);
    $excelResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
