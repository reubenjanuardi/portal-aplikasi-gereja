<?php

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use App\Services\ExcelReportService;
use App\Services\LaporanRealisasiService;
use OpenSpout\Reader\XLSX\Reader;

beforeEach(function () {
    $this->kasBesar = ChartOfAccount::create([
        'kode_akun'   => '111.01',
        'nama_akun'   => 'Kas Besar',
        'kategori'    => 'Kas & Bank',
        'is_postable' => true,
    ]);

    $this->kolekteParent = ChartOfAccount::create([
        'kode_akun'   => '21',
        'nama_akun'   => 'Kolekte & Persembahan',
        'kategori'    => 'Penerimaan',
        'is_postable' => false,
    ]);

    $this->kolekteChild = ChartOfAccount::create([
        'kode_akun'   => '21.01.01',
        'nama_akun'   => 'Kolekte Ibadah Minggu',
        'kategori'    => 'Penerimaan',
        'parent_code' => '21',
        'is_postable' => true,
    ]);

    $this->bebanParent = ChartOfAccount::create([
        'kode_akun'   => '36',
        'nama_akun'   => 'Beban Operasional',
        'kategori'    => 'Pengeluaran',
        'is_postable' => false,
    ]);

    $this->bebanChild = ChartOfAccount::create([
        'kode_akun'   => '36.01.01',
        'nama_akun'   => 'Listrik & Air',
        'kategori'    => 'Pengeluaran',
        'parent_code' => '36',
        'is_postable' => true,
    ]);
});

test('LaporanRealisasiService calculates saldo_awal and saldo_akhir for Penerimaan and Pengeluaran', function () {
    // 1. Transactions before 2026-05-10 (Saldo Awal)
    $vBkmBefore = Voucher::create([
        'no_bukti'           => 'BKM-BEF-01',
        'tanggal'            => '2026-05-02',
        'pihak_terkait'      => 'Jemaat Sektor 1',
        'jenis_voucher'      => 'BKM',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun'          => $this->kolekteChild->kode_akun,
        'total_nominal'      => 500000,
    ]);
    Transaction::create([
        'no_bukti'  => $vBkmBefore->no_bukti,
        'kode_akun' => $vBkmBefore->kode_akun,
        'uraian'    => 'Kolekte awal',
        'nominal'   => 500000,
    ]);

    $vBkkBefore = Voucher::create([
        'no_bukti'           => 'BKK-BEF-01',
        'tanggal'            => '2026-05-03',
        'pihak_terkait'      => 'PLN',
        'jenis_voucher'      => 'BKK',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun'          => $this->bebanChild->kode_akun,
        'total_nominal'      => 200000,
    ]);
    Transaction::create([
        'no_bukti'  => $vBkkBefore->no_bukti,
        'kode_akun' => $vBkkBefore->kode_akun,
        'uraian'    => 'Listrik awal',
        'nominal'   => 200000,
    ]);

    // 2. Transactions during period 2026-05-10 s/d 2026-05-15 (Realisasi)
    $vBkmDuring = Voucher::create([
        'no_bukti'           => 'BKM-DUR-01',
        'tanggal'            => '2026-05-12',
        'pihak_terkait'      => 'Jemaat Sektor 2',
        'jenis_voucher'      => 'BKM',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun'          => $this->kolekteChild->kode_akun,
        'total_nominal'      => 300000,
    ]);
    Transaction::create([
        'no_bukti'  => $vBkmDuring->no_bukti,
        'kode_akun' => $vBkmDuring->kode_akun,
        'uraian'    => 'Kolekte minggu ini',
        'nominal'   => 300000,
    ]);

    $vBkkDuring = Voucher::create([
        'no_bukti'           => 'BKK-DUR-01',
        'tanggal'            => '2026-05-13',
        'pihak_terkait'      => 'PDAM',
        'jenis_voucher'      => 'BKK',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun'          => $this->bebanChild->kode_akun,
        'total_nominal'      => 100000,
    ]);
    Transaction::create([
        'no_bukti'  => $vBkkDuring->no_bukti,
        'kode_akun' => $vBkkDuring->kode_akun,
        'uraian'    => 'Air minggu ini',
        'nominal'   => 100000,
    ]);

    $service = new LaporanRealisasiService();
    $report = $service->getWeeklyReport('2026-05-10', '2026-05-15');

    // Check Penerimaan totals
    expect((float) $report['totalSaldoAwalPenerimaan'])->toBe(500000.0)
        ->and((float) $report['totalPenerimaan'])->toBe(300000.0)
        ->and((float) $report['totalSaldoAkhirPenerimaan'])->toBe(800000.0);

    // Check Pengeluaran totals
    expect((float) $report['totalSaldoAwalPengeluaran'])->toBe(200000.0)
        ->and((float) $report['totalPengeluaran'])->toBe(100000.0)
        ->and((float) $report['totalSaldoAkhirPengeluaran'])->toBe(300000.0);

    // Check individual items
    $kolekteItem = collect($report['penerimaan'])->firstWhere('kode_akun', '21.01.01');
    expect($kolekteItem)->not->toBeNull()
        ->and((float) $kolekteItem['saldo_awal'])->toBe(500000.0)
        ->and((float) $kolekteItem['amount'])->toBe(300000.0)
        ->and((float) $kolekteItem['saldo_akhir'])->toBe(800000.0);

    $bebanItem = collect($report['pengeluaran'])->firstWhere('kode_akun', '36.01.01');
    expect($bebanItem)->not->toBeNull()
        ->and((float) $bebanItem['saldo_awal'])->toBe(200000.0)
        ->and((float) $bebanItem['amount'])->toBe(100000.0)
        ->and((float) $bebanItem['saldo_akhir'])->toBe(300000.0);
});

test('ExcelReportService generates multi-sheet Excel with Saldo Awal and Saldo Akhir columns', function () {
    $service = new LaporanRealisasiService();
    $reportData = [
        'penerimaan' => [
            [
                'kode_akun'   => '21.01.01',
                'nama_akun'   => 'Kolekte Ibadah Minggu',
                'depth'       => 2,
                'is_postable' => true,
                'saldo_awal'  => 500000,
                'saldo_akhir' => 800000,
            ],
        ],
        'totalPenerimaan' => 300000,
        'totalSaldoAwalPenerimaan' => 500000,
        'totalSaldoAkhirPenerimaan' => 800000,
        'pengeluaran' => [
            [
                'kode_akun'   => '36.01.01',
                'nama_akun'   => 'Listrik & Air',
                'depth'       => 2,
                'is_postable' => true,
                'saldo_awal'  => 200000,
                'saldo_akhir' => 300000,
            ],
        ],
        'totalPengeluaran' => 100000,
        'totalSaldoAwalPengeluaran' => 200000,
        'totalSaldoAkhirPengeluaran' => 300000,
        'kasBank' => [],
        'totalSaldoAwal' => 300000,
        'totalSaldoAkhir' => 500000,
        'surplusDefisit' => 200000,
    ];

    $excelService = app(ExcelReportService::class);
    $filePath = $excelService->generateRealisasiMingguanXlsx($reportData, '2026-05-10', '2026-05-15', '2');

    expect(file_exists($filePath))->toBeTrue();

    // Read the generated Excel using OpenSpout
    $reader = new Reader();
    $reader->open($filePath);

    $sheets = [];
    foreach ($reader->getSheetIterator() as $sheet) {
        $sheetName = $sheet->getName();
        $rows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $rows[] = $row->toArray();
        }
        $sheets[$sheetName] = $rows;
    }
    $reader->close();

    // Verify Sheet 1: Penerimaan & Ringkasan
    expect(isset($sheets['Penerimaan & Ringkasan']))->toBeTrue();
    $sheet1Rows = $sheets['Penerimaan & Ringkasan'];

    // Find table header row
    $headerRow1 = null;
    foreach ($sheet1Rows as $r) {
        if (isset($r[0]) && $r[0] === 'Kode Akun') {
            $headerRow1 = $r;
            break;
        }
    }
    expect($headerRow1)->not->toBeNull();
    expect($headerRow1[0])->toBe('Kode Akun')
        ->and($headerRow1[1])->toBe('Uraian Pos Penerimaan')
        ->and($headerRow1[2])->toBe('Tingkat Akun')
        ->and($headerRow1[3])->toBe('Saldo Awal (Rp)')
        ->and($headerRow1[4])->toBe('Saldo Akhir (Rp)');

    // Verify Sheet 2: Pengeluaran
    expect(isset($sheets['Pengeluaran']))->toBeTrue();
    $sheet2Rows = $sheets['Pengeluaran'];

    $headerRow2 = null;
    foreach ($sheet2Rows as $r) {
        if (isset($r[0]) && $r[0] === 'Kode Akun') {
            $headerRow2 = $r;
            break;
        }
    }
    expect($headerRow2)->not->toBeNull();
    expect($headerRow2[0])->toBe('Kode Akun')
        ->and($headerRow2[1])->toBe('Uraian Pos Pengeluaran')
        ->and($headerRow2[2])->toBe('Tingkat Akun')
        ->and($headerRow2[3])->toBe('Saldo Awal (Rp)')
        ->and($headerRow2[4])->toBe('Saldo Akhir (Rp)');

    @unlink($filePath);
});

test('authenticated user with permission can download Laporan Realisasi Mingguan Excel', function () {
    $this->seed(\Database\Seeders\RbacSeeder::class);
    $user = User::where('email', 'bendahara@gpibhosiana.org')->first();

    $response = $this->actingAs($user)->get(route('laporan.realisasi-mingguan.excel', [
        'startDate' => '2026-05-10',
        'endDate'   => '2026-05-15',
        'mingguKe'  => '2',
    ]));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
