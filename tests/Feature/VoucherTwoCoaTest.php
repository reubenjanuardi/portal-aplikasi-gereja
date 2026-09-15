<?php

use App\Filament\Resources\VoucherResource;
use App\Http\Controllers\VoucherPdfController;
use App\Models\AppSetting;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use App\Services\LaporanRealisasiService;

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

    // 2. Penerimaan (Income) account
    $this->kolekte = ChartOfAccount::create([
        'kode_akun'   => '21.01.01',
        'nama_akun'   => 'Kolekte Ibadah Hari Minggu Pagi',
        'kategori'    => 'Penerimaan',
        'is_postable' => true,
    ]);

    // 3. Pengeluaran (Expense) account
    $this->atk = ChartOfAccount::create([
        'kode_akun'   => '363.01.01',
        'nama_akun'   => 'ATK & Fotocopy',
        'kategori'    => 'Pengeluaran',
        'is_postable' => true,
    ]);
});

test('can create a voucher with both kas/bank account and budget account (2 CoA inputs)', function () {
    // Test BKK (Kas Keluar): Sumber Kas Besar, Tujuan Beban ATK
    $voucherBkk = Voucher::create([
        'no_bukti'           => 'BKK-2026-001',
        'tanggal'            => '2026-09-09',
        'pihak_terkait'      => 'Toko Buku Gramedia',
        'jenis_voucher'      => 'BKK',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun'          => $this->atk->kode_akun,
        'total_nominal'      => 150000,
    ]);

    expect($voucherBkk->akunKasBank->kode_akun)->toBe('111.01')
        ->and($voucherBkk->akunKasBank->nama_akun)->toBe('Kas Besar')
        ->and($voucherBkk->chartOfAccount->kode_akun)->toBe('363.01.01')
        ->and($voucherBkk->chartOfAccount->nama_akun)->toBe('ATK & Fotocopy');

    // Test BBK (Bank Keluar): Sumber Bank BRI, Tujuan Beban ATK
    $voucherBbk = Voucher::create([
        'no_bukti'           => 'BBK-2026-001',
        'tanggal'            => '2026-09-09',
        'pihak_terkait'      => 'Supplier Komputer',
        'jenis_voucher'      => 'BBK',
        'kode_akun_kas_bank' => $this->bankBri->kode_akun,
        'kode_akun'          => $this->atk->kode_akun,
        'total_nominal'      => 2500000,
    ]);

    expect($voucherBbk->akunKasBank->kode_akun)->toBe('112.02')
        ->and($voucherBbk->isBank())->toBeTrue()
        ->and($voucherBbk->isKeluar())->toBeTrue();

    // Test BKM (Kas Masuk): Penerima Kas Besar, Sumber Kolekte
    $voucherBkm = Voucher::create([
        'no_bukti'           => 'BKM-2026-001',
        'tanggal'            => '2026-09-09',
        'pihak_terkait'      => 'Jemaat Sektor 1',
        'jenis_voucher'      => 'BKM',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun'          => $this->kolekte->kode_akun,
        'total_nominal'      => 750000,
    ]);

    expect($voucherBkm->akunKasBank->kode_akun)->toBe('111.01')
        ->and($voucherBkm->chartOfAccount->kode_akun)->toBe('21.01.01')
        ->and($voucherBkm->isMasuk())->toBeTrue();

    // Test BBM (Bank Masuk): Penerima Bank BRI, Sumber Kolekte
    $voucherBbm = Voucher::create([
        'no_bukti'           => 'BBM-2026-001',
        'tanggal'            => '2026-09-09',
        'pihak_terkait'      => 'Transfer Jemaat',
        'jenis_voucher'      => 'BBM',
        'kode_akun_kas_bank' => $this->bankBri->kode_akun,
        'kode_akun'          => $this->kolekte->kode_akun,
        'total_nominal'      => 1000000,
    ]);

    expect($voucherBbm->akunKasBank->kode_akun)->toBe('112.02')
        ->and($voucherBbm->chartOfAccount->kode_akun)->toBe('21.01.01')
        ->and($voucherBbm->isBank())->toBeTrue()
        ->and($voucherBbm->isMasuk())->toBeTrue();
});

test('VoucherResource::getKasBankOptions filters accounts based on voucher type', function () {
    // For BKK and BKM (Kas): Should include Kas accounts
    $kasOptions = VoucherResource::getKasBankOptions('BKK');
    expect(array_keys($kasOptions))->toContain('111.01')
        ->and(array_keys($kasOptions))->not->toContain('112.02');

    // For BBK and BBM (Bank): Should include Bank accounts
    $bankOptions = VoucherResource::getKasBankOptions('BBK');
    expect(array_keys($bankOptions))->toContain('112.02')
        ->and(array_keys($bankOptions))->not->toContain('111.01');
});

test('VoucherResource::getMataAnggaranTreeOptions filters accounts based on voucher direction', function () {
    // For BKK (Expense): Should contain Pengeluaran and not Penerimaan
    $expenseOptions = VoucherResource::getMataAnggaranTreeOptions('BKK');
    expect(array_keys($expenseOptions))->toContain('363.01.01')
        ->and(array_keys($expenseOptions))->not->toContain('21.01.01');

    // For BBM (Income): Should contain Penerimaan and not Pengeluaran
    $incomeOptions = VoucherResource::getMataAnggaranTreeOptions('BBM');
    expect(array_keys($incomeOptions))->toContain('21.01.01')
        ->and(array_keys($incomeOptions))->not->toContain('363.01.01');
});

test('PDF voucher stream renders with both Kas/Bank and Anggaran account info', function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    $voucher = Voucher::create([
        'no_bukti'           => 'BBK-PDF-001',
        'tanggal'            => '2026-09-09',
        'pihak_terkait'      => 'PT PLN Persero',
        'jenis_voucher'      => 'BBK',
        'kode_akun_kas_bank' => $this->bankBri->kode_akun,
        'kode_akun'          => $this->atk->kode_akun,
        'total_nominal'      => 500000,
    ]);

    Transaction::create([
        'no_bukti'  => $voucher->no_bukti,
        'kode_akun' => $voucher->kode_akun,
        'uraian'    => 'Pembayaran operasional',
        'nominal'   => 500000,
    ]);

    $response = $this->actingAs($user)->get(route('vouchers.pdf', $voucher));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('LaporanRealisasiService accurately calculates Kas and Bank balances using vouchers.kode_akun_kas_bank', function () {
    // Create an outgoing bank voucher (BBK) of 200,000 from Bank BRI
    $voucher = Voucher::create([
        'no_bukti'           => 'BBK-RLS-001',
        'tanggal'            => '2026-09-09',
        'pihak_terkait'      => 'Vendor X',
        'jenis_voucher'      => 'BBK',
        'kode_akun_kas_bank' => $this->bankBri->kode_akun,
        'kode_akun'          => $this->atk->kode_akun,
        'total_nominal'      => 200000,
    ]);

    Transaction::create([
        'no_bukti'  => $voucher->no_bukti,
        'kode_akun' => $voucher->kode_akun,
        'uraian'    => 'Pengeluaran via Bank BRI',
        'nominal'   => 200000,
    ]);

    $service = new LaporanRealisasiService();
    $report = $service->getWeeklyReport('2026-09-01', '2026-09-30');

    // Find the Bank BRI entry in kasBank report
    $briEntry = collect($report['kasBank'])->firstWhere('kode_akun', '112.02');
    expect($briEntry)->not->toBeNull();
    // Since it was a BBK (Keluar), saldo_akhir should be -200000 (starting from 0)
    expect((float) $briEntry['saldo_akhir'])->toBe(-200000.0);
});
