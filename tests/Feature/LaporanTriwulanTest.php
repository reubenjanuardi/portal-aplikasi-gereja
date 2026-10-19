<?php

use App\Models\ActivityLog;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use App\Services\ExcelReportService;
use App\Services\LaporanTriwulanService;
use Database\Seeders\RbacSeeder;
use OpenSpout\Reader\XLSX\Reader;

beforeEach(function () {
    $this->seed(RbacSeeder::class);
    $this->bendahara = User::where('email', 'bendahara@gpibhosiana.org')->first();

    // CoA untuk uji
    $this->kasBesar = ChartOfAccount::create([
        'kode_akun' => '111.01',
        'nama_akun' => 'Kas Besar',
        'kategori' => 'Kas & Bank',
        'is_postable' => true,
    ]);

    $this->penerimaanParent = ChartOfAccount::create([
        'kode_akun' => '21',
        'nama_akun' => 'Kolekte & Persembahan',
        'kategori' => 'Penerimaan',
        'is_postable' => false,
        'budget' => 0,
    ]);
    $this->kolekteChild = ChartOfAccount::create([
        'kode_akun' => '21.01.01',
        'nama_akun' => 'Kolekte Ibadah Minggu',
        'kategori' => 'Penerimaan',
        'parent_code' => '21',
        'is_postable' => true,
        'budget' => 5000000,
    ]);

    $this->pengeluaranParent = ChartOfAccount::create([
        'kode_akun' => '36',
        'nama_akun' => 'Beban Operasional',
        'kategori' => 'Pengeluaran',
        'is_postable' => false,
        'budget' => 0,
    ]);
    $this->bebanChild = ChartOfAccount::create([
        'kode_akun' => '36.01.01',
        'nama_akun' => 'Listrik & Air',
        'kategori' => 'Pengeluaran',
        'parent_code' => '36',
        'is_postable' => true,
        'budget' => 2000000,
    ]);
});

test('LaporanTriwulanService resolves fiscal quarters correctly', function () {
    $service = new LaporanTriwulanService;

    // TA 2026: Q1 Apr-Jun 2026
    $q1 = $service->resolvePeriod(2026, 1);
    expect($q1['tahun_label'])->toBe('2026–2027')
        ->and($q1['triwulan_label'])->toBe('Triwulan I')
        ->and($q1['start_date'])->toBe('2026-04-01')
        ->and($q1['end_date'])->toBe('2026-06-30')
        ->and($q1['persen_anggaran'])->toBe(0.25)
        ->and(count($q1['months']))->toBe(3)
        ->and($q1['months'][0]['key'])->toBe('2026-04')
        ->and($q1['months'][1]['key'])->toBe('2026-05')
        ->and($q1['months'][2]['key'])->toBe('2026-06');

    // Q2 Jul-Sep 2026
    $q2 = $service->resolvePeriod(2026, 2);
    expect($q2['start_date'])->toBe('2026-07-01')
        ->and($q2['end_date'])->toBe('2026-09-30')
        ->and($q2['persen_anggaran'])->toBe(0.50)
        ->and($q2['months'][0]['key'])->toBe('2026-07');

    // Q3 Oct-Dec 2026
    $q3 = $service->resolvePeriod(2026, 3);
    expect($q3['start_date'])->toBe('2026-10-01')
        ->and($q3['end_date'])->toBe('2026-12-31')
        ->and($q3['persen_anggaran'])->toBe(0.75);

    // Q4 Jan-Mar 2027 (cross-year)
    $q4 = $service->resolvePeriod(2026, 4);
    expect($q4['start_date'])->toBe('2027-01-01')
        ->and($q4['end_date'])->toBe('2027-03-31')
        ->and($q4['persen_anggaran'])->toBe(1.00)
        ->and($q4['months'][0]['key'])->toBe('2027-01');
});

test('LaporanTriwulanService includes transactions at period boundaries', function () {
    // Voucher tepat pada batas Q1 2026
    $vBkmQ1End = Voucher::create([
        'no_bukti' => 'BKM-Q1-END',
        'tanggal' => '2026-06-30', // 30 Juni -> dalam Q1
        'pihak_terkait' => 'Jemaat',
        'jenis_voucher' => 'BKM',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun' => $this->kolekteChild->kode_akun,
        'total_nominal' => 100000,
    ]);
    Transaction::create([
        'no_bukti' => $vBkmQ1End->no_bukti,
        'kode_akun' => $vBkmQ1End->kode_akun,
        'uraian' => 'Kolekte 30 Juni',
        'nominal' => 100000,
    ]);

    // Voucher tepat awal Q2 2026 (1 Juli)
    $vBkmQ2Start = Voucher::create([
        'no_bukti' => 'BKM-Q2-START',
        'tanggal' => '2026-07-01',
        'pihak_terkait' => 'Jemaat',
        'jenis_voucher' => 'BKM',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun' => $this->kolekteChild->kode_akun,
        'total_nominal' => 100000,
    ]);
    Transaction::create([
        'no_bukti' => $vBkmQ2Start->no_bukti,
        'kode_akun' => $vBkmQ2Start->kode_akun,
        'uraian' => 'Kolekte 1 Juli',
        'nominal' => 100000,
    ]);

    // Voucher awal Q1 (1 April)
    $vBkmQ1Start = Voucher::create([
        'no_bukti' => 'BKM-Q1-START',
        'tanggal' => '2026-04-01',
        'pihak_terkait' => 'Jemaat',
        'jenis_voucher' => 'BKM',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun' => $this->kolekteChild->kode_akun,
        'total_nominal' => 50000,
    ]);
    Transaction::create([
        'no_bukti' => $vBkmQ1Start->no_bukti,
        'kode_akun' => $vBkmQ1Start->kode_akun,
        'uraian' => 'Kolekte 1 April',
        'nominal' => 50000,
    ]);

    $service = new LaporanTriwulanService;

    // Q1 harus mencakup 1 April dan 30 Juni, total 150000
    $reportQ1 = $service->getReport(2026, 1);
    $itemQ1 = collect($reportQ1['detail'])->firstWhere('kode_akun', '21.01.01');
    expect($itemQ1)->not->toBeNull()
        ->and($itemQ1['realisasi'])->toBe(150000.0)
        ->and($itemQ1['monthly']['2026-04'])->toBe(50000.0)
        ->and($itemQ1['monthly']['2026-06'])->toBe(100000.0);

    // Q2 hanya mencakup 1 Juli, total 100000
    $reportQ2 = $service->getReport(2026, 2);
    $itemQ2 = collect($reportQ2['detail'])->firstWhere('kode_akun', '21.01.01');
    expect($itemQ2)->not->toBeNull()
        ->and($itemQ2['realisasi'])->toBe(100000.0)
        ->and($itemQ2['monthly']['2026-07'])->toBe(100000.0);
});

test('LaporanTriwulanService rollup: parent sums children without double counting', function () {
    // Child 1
    $v1 = Voucher::create([
        'no_bukti' => 'BKM-C1',
        'tanggal' => '2026-05-10',
        'pihak_terkait' => 'Jemaat',
        'jenis_voucher' => 'BKM',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun' => $this->kolekteChild->kode_akun,
        'total_nominal' => 300000,
    ]);
    Transaction::create([
        'no_bukti' => $v1->no_bukti,
        'kode_akun' => $v1->kode_akun,
        'uraian' => 'Child 1',
        'nominal' => 300000,
    ]);

    // Child 2: tambah akun anak 21.01.02
    $child2 = ChartOfAccount::create([
        'kode_akun' => '21.01.02',
        'nama_akun' => 'Kolekte Khusus',
        'kategori' => 'Penerimaan',
        'parent_code' => '21',
        'is_postable' => true,
        'budget' => 2000000,
    ]);
    $v2 = Voucher::create([
        'no_bukti' => 'BKM-C2',
        'tanggal' => '2026-05-15',
        'pihak_terkait' => 'Jemaat',
        'jenis_voucher' => 'BKM',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun' => $child2->kode_akun,
        'total_nominal' => 200000,
    ]);
    Transaction::create([
        'no_bukti' => $v2->no_bukti,
        'kode_akun' => $v2->kode_akun,
        'uraian' => 'Child 2',
        'nominal' => 200000,
    ]);

    $service = new LaporanTriwulanService;
    $report = $service->getReport(2026, 1); // Mei dalam Q1

    // Parent 21 harus menjumlahkan child: 300k + 200k = 500k
    $parent = collect($report['detail'])->firstWhere('kode_akun', '21');
    expect($parent)->not->toBeNull()
        ->and($parent['realisasi'])->toBe(500000.0)
        ->and($parent['monthly']['2026-05'])->toBe(500000.0);

    // Child 1 individual
    $c1 = collect($report['detail'])->firstWhere('kode_akun', '21.01.01');
    expect($c1)->not->toBeNull()->and($c1['realisasi'])->toBe(300000.0);

    // Child 2
    $c2 = collect($report['detail'])->firstWhere('kode_akun', '21.01.02');
    expect($c2)->not->toBeNull()->and($c2['realisasi'])->toBe(200000.0);

    // Total penerimaan di rekap = 500k
    expect($report['rekap']['penerimaan']['total_realisasi'])->toBe(500000.0);
});

test('guest cannot access Laporan Triwulan page or exports', function () {
    $this->get('/keuangan/laporan-triwulan')->assertRedirect();
    $this->get(route('laporan.triwulan.pdf'))->assertRedirect(route('login'));
    $this->get(route('laporan.triwulan.excel'))->assertRedirect(route('login'));
});

test('authenticated user can view Laporan Triwulan page', function () {
    $this->actingAs($this->bendahara)
        ->get('/keuangan/laporan-triwulan')
        ->assertStatus(200);
});

test('authenticated user with export permission can download PDF and Excel', function () {
    // Seed minimal data to ensure route works
    $v = Voucher::create([
        'no_bukti' => 'BKM-TEST',
        'tanggal' => '2026-05-15',
        'pihak_terkait' => 'Jemaat',
        'jenis_voucher' => 'BKM',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun' => $this->kolekteChild->kode_akun,
        'total_nominal' => 100000,
    ]);
    Transaction::create([
        'no_bukti' => $v->no_bukti,
        'kode_akun' => $v->kode_akun,
        'uraian' => 'Test',
        'nominal' => 100000,
    ]);

    // PDF
    $pdf = $this->actingAs($this->bendahara)
        ->get(route('laporan.triwulan.pdf', ['tahunAnggaran' => 2026, 'triwulan' => 1]));
    $pdf->assertStatus(200);
    $pdf->assertHeader('content-type', 'application/pdf');

    // Excel
    $excel = $this->actingAs($this->bendahara)
        ->get(route('laporan.triwulan.excel', ['tahunAnggaran' => 2026, 'triwulan' => 1]));
    $excel->assertStatus(200);
    $excel->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    // Activity log recorded
    expect(ActivityLog::where('description', 'Mengunduh Laporan Triwulan PDF')->exists())->toBeTrue()
        ->and(ActivityLog::where('description', 'Mengunduh Laporan Triwulan Excel')->exists())->toBeTrue();
});

test('ExcelReportService generates Triwulan workbook with Rekapitulasi and Detail sheets', function () {
    $service = new LaporanTriwulanService;
    $reportData = $service->getReport(2026, 1); // may be empty

    $excelService = app(ExcelReportService::class);
    $filePath = $excelService->generateTriwulanXlsx($reportData);

    expect(file_exists($filePath))->toBeTrue();

    $reader = new Reader;
    $reader->open($filePath);

    $sheetNames = [];
    foreach ($reader->getSheetIterator() as $sheet) {
        $sheetNames[] = $sheet->getName();
    }
    $reader->close();

    expect($sheetNames)->toContain('Rekapitulasi')
        ->and($sheetNames)->toContain('Detail');

    @unlink($filePath);
});

test('Excel Rekapitulasi sheet has correct headers and totals', function () {
    // Buat data dengan transaksi
    $v = Voucher::create([
        'no_bukti' => 'BKM-REK',
        'tanggal' => '2026-05-10',
        'pihak_terkait' => 'Jemaat',
        'jenis_voucher' => 'BKM',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun' => $this->kolekteChild->kode_akun,
        'total_nominal' => 750000,
    ]);
    Transaction::create([
        'no_bukti' => $v->no_bukti,
        'kode_akun' => $v->kode_akun,
        'uraian' => 'Kolekte Mei',
        'nominal' => 750000,
    ]);

    $service = new LaporanTriwulanService;
    $reportData = $service->getReport(2026, 1);
    $excelService = app(ExcelReportService::class);
    $filePath = $excelService->generateTriwulanXlsx($reportData);

    $reader = new Reader;
    $reader->open($filePath);
    $sheets = [];
    foreach ($reader->getSheetIterator() as $sheet) {
        $sheetNames = $sheet->getName();
        $rows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $rows[] = $row->toArray();
        }
        $sheets[$sheetNames] = $rows;
    }
    $reader->close();

    $rekap = $sheets['Rekapitulasi'];
    // Cari baris header Penerimaan
    $headerIdx = null;
    foreach ($rekap as $i => $r) {
        if (isset($r[0]) && $r[0] === 'No. MA') {
            $headerIdx = $i;
            break;
        }
    }
    expect($headerIdx)->not->toBeNull();
    $header = $rekap[$headerIdx];
    expect($header[0])->toBe('No. MA')
        ->and($header[1])->toBe('Uraian Anggaran')
        ->and($header[2])->toBe('Anggaran Setahun (Rp)')
        ->and($header[3])->toBe('Realisasi Triwulan (Rp)');

    // Total row near end
    $totalRow = null;
    foreach ($rekap as $r) {
        if (isset($r[0]) && is_string($r[0]) && str_contains($r[0], 'TOTAL PENERIMAAN')) {
            $totalRow = $r;
            break;
        }
    }
    expect($totalRow)->not->toBeNull();
    expect((float) $totalRow[2])->toBe(5000000.0) // budget parent 5M
        ->and((float) $totalRow[3])->toBe(750000.0); // realisasi

    @unlink($filePath);
});

test('Laporan Triwulan empty Q1 2026 shows zeros but page loads', function () {
    // TA 2026 belum punya data pada Q1 (sistem mulai Q2) — halaman tetap load.
    $this->assertSame(2026, LaporanTriwulanService::fiscalYearOf('2026-05-10'));

    $response = $this->actingAs($this->bendahara)->get('/keuangan/laporan-triwulan');
    $response->assertStatus(200);

    $report = app(LaporanTriwulanService::class)->getReport(2026, 1);
    expect($report['totalPenerimaan'])->toBe(0.0)
        ->and($report['totalPengeluaran'])->toBe(0.0)
        ->and($report['period']['start_date'])->toBe('2026-04-01');
});

test('LaporanTriwulanService Real to Plan memakai akumulasi triwulan', function () {
    // Budget tahunan 12.000.000 -> anggaran triwulan Q1 (25%) = 3.000.000
    $this->kolekteChild->update(['budget' => 12000000]);

    $v = Voucher::create([
        'no_bukti' => 'BKM-PCT',
        'tanggal' => '2026-05-15',
        'pihak_terkait' => 'Jemaat',
        'jenis_voucher' => 'BKM',
        'kode_akun_kas_bank' => $this->kasBesar->kode_akun,
        'kode_akun' => $this->kolekteChild->kode_akun,
        'total_nominal' => 500000,
    ]);
    Transaction::create([
        'no_bukti' => $v->no_bukti,
        'kode_akun' => $v->kode_akun,
        'uraian' => 'Test pct',
        'nominal' => 500000,
    ]);

    $service = new LaporanTriwulanService;
    $report = $service->getReport(2026, 1);

    $item = collect($report['detail'])->firstWhere('kode_akun', '21.01.01');

    // Satu kolom Real to Plan: realisasi kumulatif triwulan / anggaran kumulatif triwulan
    expect($item['anggaran_triwulan'])->toBe(3000000.0)
        ->and(round($item['persen'], 4))->toBe(16.6667)
        ->and(array_key_exists('persen_monthly', $item))->toBeFalse();

    // Q2 (Jul-Sep): anggaran kumulatif 50% = 6.000.000, realisasi Jul-Sep = 0 -> 0%
    $reportQ2 = $service->getReport(2026, 2);
    $itemQ2 = collect($reportQ2['detail'])->firstWhere('kode_akun', '21.01.01');
    expect($itemQ2['anggaran_triwulan'])->toBe(6000000.0)
        ->and((float) $itemQ2['persen'])->toBe(0.0);
});
