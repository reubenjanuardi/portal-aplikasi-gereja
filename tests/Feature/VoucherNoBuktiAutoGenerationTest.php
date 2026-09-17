<?php

use App\Filament\Resources\VoucherResource;
use App\Models\ChartOfAccount;
use App\Models\Voucher;

beforeEach(function () {
    $this->kasBesar = ChartOfAccount::firstOrCreate(
        ['kode_akun' => '111.01'],
        [
            'nama_akun'   => 'Kas Besar',
            'kategori'    => 'Kas & Bank',
            'is_postable' => true,
        ]
    );

    $this->atk = ChartOfAccount::firstOrCreate(
        ['kode_akun' => '363.01.01'],
        [
            'nama_akun'   => 'ATK & Fotocopy',
            'kategori'    => 'Pengeluaran',
            'is_postable' => true,
        ]
    );
});

test('getRomawiMonth correctly converts month integer to Roman numeral', function () {
    expect(VoucherResource::getRomawiMonth(1))->toBe('I')
        ->and(VoucherResource::getRomawiMonth(4))->toBe('IV')
        ->and(VoucherResource::getRomawiMonth(5))->toBe('V')
        ->and(VoucherResource::getRomawiMonth(9))->toBe('IX')
        ->and(VoucherResource::getRomawiMonth(10))->toBe('X')
        ->and(VoucherResource::getRomawiMonth(12))->toBe('XII');
});

test('generateNoBukti generates 001 for a new week without existing records', function () {
    // 2026-11-02 is Monday, Day 2 of Nov -> Week 01, Month XI, Year 2026
    $noBukti = VoucherResource::generateNoBukti('BKK', '2026-11-02');
    expect($noBukti)->toBe('BKK001-01-XI-2026');
});

test('generateNoBukti sequentially increments within the same week', function () {
    // Week 1 November 2026
    Voucher::create([
        'no_bukti'           => 'BKK001-01-XI-2026',
        'tanggal'            => '2026-11-01',
        'pihak_terkait'      => 'Vendor 1',
        'jenis_voucher'      => 'BKK',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun'          => $this->atk->kode_akun,
        'total_nominal'      => 100000,
    ]);

    $nextNoBukti = VoucherResource::generateNoBukti('BKK', '2026-11-03');
    expect($nextNoBukti)->toBe('BKK002-01-XI-2026');

    Voucher::create([
        'no_bukti'           => 'BKK002-01-XI-2026',
        'tanggal'            => '2026-11-03',
        'pihak_terkait'      => 'Vendor 2',
        'jenis_voucher'      => 'BKK',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun'          => $this->atk->kode_akun,
        'total_nominal'      => 200000,
    ]);

    $nextNoBukti2 = VoucherResource::generateNoBukti('BKK', '2026-11-05');
    expect($nextNoBukti2)->toBe('BKK003-01-XI-2026');
});

test('generateNoBukti handles backdating to a different week or month accurately', function () {
    // Given Week 2 of November 2026
    $noBuktiNovW2 = VoucherResource::generateNoBukti('BKK', '2026-11-10');
    expect($noBuktiNovW2)->toBe('BKK001-02-XI-2026');

    // Backdate to Week 4 of October 2026 (e.g. 2026-10-25 -> Week 04, X, 2026)
    $noBuktiOctW4 = VoucherResource::generateNoBukti('BKK', '2026-10-25');
    expect($noBuktiOctW4)->toBe('BKK001-04-X-2026');
});

test('different voucher types maintain independent sequences within the same week', function () {
    Voucher::create([
        'no_bukti'           => 'BKK001-01-XII-2026',
        'tanggal'            => '2026-12-01',
        'pihak_terkait'      => 'Vendor 1',
        'jenis_voucher'      => 'BKK',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun'          => $this->atk->kode_akun,
        'total_nominal'      => 50000,
    ]);

    // BKM in the same week should start at 001, not 002
    $bkmNoBukti = VoucherResource::generateNoBukti('BKM', '2026-12-01');
    expect($bkmNoBukti)->toBe('BKM001-01-XII-2026');

    // BBM in the same week should start at 001
    $bbmNoBukti = VoucherResource::generateNoBukti('BBM', '2026-12-02');
    expect($bbmNoBukti)->toBe('BBM001-01-XII-2026');

    // BBK in the same week should start at 001
    $bbkNoBukti = VoucherResource::generateNoBukti('BBK', '2026-12-03');
    expect($bbkNoBukti)->toBe('BBK001-01-XII-2026');
});

test('generateNoBukti recognizes legacy 2-digit sequence numbers when calculating next number', function () {
    Voucher::create([
        'no_bukti'           => 'BKK05-01-VIII-2026',
        'tanggal'            => '2026-08-02',
        'pihak_terkait'      => 'Vendor Legacy',
        'jenis_voucher'      => 'BKK',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun'          => $this->atk->kode_akun,
        'total_nominal'      => 120000,
    ]);

    $nextNoBukti = VoucherResource::generateNoBukti('BKK', '2026-08-05');
    expect($nextNoBukti)->toBe('BKK006-01-VIII-2026');
});
