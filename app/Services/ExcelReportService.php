<?php

namespace App\Services;

use App\Models\AppSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

class ExcelReportService
{
    /**
     * Common Styles
     */
    protected function getTitleStyle(): Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontSize(14)
            ->setFontColor(Color::BLACK);
    }

    protected function getSubtitleStyle(): Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor(Color::DARK_BLUE);
    }

    protected function getMetaStyle(): Style
    {
        return (new Style())
            ->setFontItalic()
            ->setFontSize(10)
            ->setFontColor('64748B');
    }

    protected function getTableHeaderStyle(): Style
    {
        $border = new Border(
            new BorderPart(Border::TOP, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_MEDIUM, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
        );

        return (new Style())
            ->setFontBold()
            ->setFontSize(10)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('1E3A8A') // Navy Blue
            ->setBorder($border);
    }

    protected function getSectionHeaderStyle(): Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor('1E3A8A')
            ->setBackgroundColor('E2E8F0'); // Light Slate
    }

    protected function getDataRowStyle(): Style
    {
        $border = new Border(
            new BorderPart(Border::TOP, 'E2E8F0', Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, 'E2E8F0', Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, 'E2E8F0', Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT, 'E2E8F0', Border::WIDTH_THIN, Border::STYLE_SOLID)
        );

        return (new Style())
            ->setFontSize(10)
            ->setBorder($border);
    }

    protected function getNumberStyle(): Style
    {
        $border = new Border(
            new BorderPart(Border::TOP, 'E2E8F0', Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, 'E2E8F0', Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, 'E2E8F0', Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT, 'E2E8F0', Border::WIDTH_THIN, Border::STYLE_SOLID)
        );

        return (new Style())
            ->setFontSize(10)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setBorder($border);
    }

    protected function getSubtotalRowStyle(): Style
    {
        $border = new Border(
            new BorderPart(Border::TOP, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
        );

        return (new Style())
            ->setFontBold()
            ->setFontSize(10)
            ->setBackgroundColor('F1F5F9')
            ->setBorder($border);
    }

    protected function getSubtotalNumberStyle(): Style
    {
        $border = new Border(
            new BorderPart(Border::TOP, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
        );

        return (new Style())
            ->setFontBold()
            ->setFontSize(10)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setBackgroundColor('F1F5F9')
            ->setBorder($border);
    }

    protected function getGrandTotalRowStyle(): Style
    {
        $border = new Border(
            new BorderPart(Border::TOP, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_MEDIUM, Border::STYLE_DOUBLE)
        );

        return (new Style())
            ->setFontBold()
            ->setFontSize(11)
            ->setBackgroundColor('CBD5E1')
            ->setBorder($border);
    }

    protected function getGrandTotalNumberStyle(): Style
    {
        $border = new Border(
            new BorderPart(Border::TOP, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_MEDIUM, Border::STYLE_DOUBLE)
        );

        return (new Style())
            ->setFontBold()
            ->setFontSize(11)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setBackgroundColor('CBD5E1')
            ->setBorder($border);
    }

    /**
     * Generate XLSX for Laporan Buku Besar
     */
    public function generateBukuBesarXlsx(
        Collection $reportData,
        string $startDate,
        string $endDate,
        ?string $kodeAkun = null,
        ?string $jenisVoucher = null
    ): string {
        $tempPath = tempnam(sys_get_temp_dir(), 'buku_besar_') . '.xlsx';
        $options = new Options();
        $writer = new Writer($options);
        $writer->openToFile($tempPath);

        $churchName = AppSetting::get('church_name', 'GPIB JEMAAT HOSIANA');
        $churchAddress1 = AppSetting::get('church_address1', '');
        $churchAddress2 = AppSetting::get('church_address2', '');

        // Title Section
        $writer->addRow(Row::fromValues([strtoupper($churchName)], $this->getTitleStyle()));
        if ($churchAddress1 || $churchAddress2) {
            $writer->addRow(Row::fromValues([trim("{$churchAddress1} {$churchAddress2}")], $this->getMetaStyle()));
        }
        $writer->addRow(Row::fromValues(['LAPORAN BUKU BESAR'], $this->getSubtitleStyle()));

        $formattedPeriod = 'Periode: ' . Carbon::parse($startDate)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($endDate)->translatedFormat('d F Y');
        $writer->addRow(Row::fromValues([$formattedPeriod], $this->getMetaStyle()));

        $filterInfo = 'Filter: ' . ($kodeAkun ? "Akun: {$kodeAkun}" : 'Semua Akun') . ' | ' . ($jenisVoucher ? "Jenis: {$jenisVoucher}" : 'Semua Jenis Voucher');
        $writer->addRow(Row::fromValues([$filterInfo], $this->getMetaStyle()));
        $writer->addRow(Row::fromValues([''])); // Blank row

        $grandDebit = 0;
        $grandKredit = 0;

        foreach ($reportData as $accCode => $transactions) {
            $firstTx = $transactions->first();
            $coaName = $firstTx?->chartOfAccount?->nama_akun ?? $accCode;
            $kategori = $firstTx?->chartOfAccount?->kategori ?? '-';

            // Account Header
            $writer->addRow(Row::fromValues([
                "AKUN: {$accCode} - {$coaName} (Kategori: {$kategori})",
                '', '', '', '', '', ''
            ], $this->getSectionHeaderStyle()));

            // Table Column Headers
            $writer->addRow(Row::fromValues([
                'Tanggal',
                'No. Bukti Voucher',
                'Jenis Voucher',
                'Uraian / Keterangan Transaksi',
                'Debit (Rp)',
                'Kredit (Rp)',
                'Saldo Akumulasi (Rp)',
            ], $this->getTableHeaderStyle()));

            $runningBalance = 0;
            $subDebit = 0;
            $subKredit = 0;

            foreach ($transactions as $tx) {
                $voucher = $tx->voucher;
                $tgl = $voucher?->tanggal ? Carbon::parse($voucher->tanggal)->translatedFormat('d/m/Y') : '-';
                $noBukti = $tx->no_bukti ?? '-';
                $jenis = $voucher?->jenis_voucher ?? '-';
                $uraian = $tx->uraian ?: ($voucher?->keterangan ?? '-');
                $nominal = (float) $tx->nominal;

                // Tentukan Debit / Kredit berdasarkan jenis voucher atau kategori
                $isDebit = in_array($jenis, ['BKK', 'BBK', 'Keluar']) || in_array($kategori, ['Pengeluaran']);
                $debit = $isDebit ? $nominal : 0;
                $kredit = ! $isDebit ? $nominal : 0;

                $subDebit += $debit;
                $subKredit += $kredit;

                // Hitung running balance
                if ($kategori === 'Penerimaan') {
                    $runningBalance += ($kredit - $debit);
                } else {
                    $runningBalance += ($debit - $kredit);
                }

                $cells = [
                    Cell::fromValue($tgl, $this->getDataRowStyle()),
                    Cell::fromValue($noBukti, $this->getDataRowStyle()),
                    Cell::fromValue($jenis, $this->getDataRowStyle()),
                    Cell::fromValue($uraian, $this->getDataRowStyle()),
                    Cell::fromValue($debit, $this->getNumberStyle()),
                    Cell::fromValue($kredit, $this->getNumberStyle()),
                    Cell::fromValue($runningBalance, $this->getNumberStyle()),
                ];
                $writer->addRow(new Row($cells));
            }

            $grandDebit += $subDebit;
            $grandKredit += $subKredit;

            // Subtotal Row per Akun
            $subtotalCells = [
                Cell::fromValue("Subtotal Akun {$accCode}", $this->getSubtotalRowStyle()),
                Cell::fromValue('', $this->getSubtotalRowStyle()),
                Cell::fromValue('', $this->getSubtotalRowStyle()),
                Cell::fromValue('', $this->getSubtotalRowStyle()),
                Cell::fromValue($subDebit, $this->getSubtotalNumberStyle()),
                Cell::fromValue($subKredit, $this->getSubtotalNumberStyle()),
                Cell::fromValue($runningBalance, $this->getSubtotalNumberStyle()),
            ];
            $writer->addRow(new Row($subtotalCells));
            $writer->addRow(Row::fromValues([''])); // Blank row after account
        }

        // Grand Total Section
        $grandCells = [
            Cell::fromValue('GRAND TOTAL KESELURUHAN', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue($grandDebit, $this->getGrandTotalNumberStyle()),
            Cell::fromValue($grandKredit, $this->getGrandTotalNumberStyle()),
            Cell::fromValue($grandDebit - $grandKredit, $this->getGrandTotalNumberStyle()),
        ];
        $writer->addRow(new Row($grandCells));

        $writer->close();

        return $tempPath;
    }

    /**
     * Generate XLSX for Laporan Jurnal Transaksi
     */
    public function generateJurnalXlsx(
        Collection $reportData,
        string $startDate,
        string $endDate,
        ?string $kategori = null
    ): string {
        $tempPath = tempnam(sys_get_temp_dir(), 'jurnal_') . '.xlsx';
        $options = new Options();
        $writer = new Writer($options);
        $writer->openToFile($tempPath);

        $churchName = AppSetting::get('church_name', 'GPIB JEMAAT HOSIANA');
        $churchAddress1 = AppSetting::get('church_address1', '');
        $churchAddress2 = AppSetting::get('church_address2', '');

        // Title Section
        $writer->addRow(Row::fromValues([strtoupper($churchName)], $this->getTitleStyle()));
        if ($churchAddress1 || $churchAddress2) {
            $writer->addRow(Row::fromValues([trim("{$churchAddress1} {$churchAddress2}")], $this->getMetaStyle()));
        }
        $writer->addRow(Row::fromValues(['LAPORAN JURNAL TRANSAKSI'], $this->getSubtitleStyle()));

        $formattedPeriod = 'Periode: ' . Carbon::parse($startDate)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($endDate)->translatedFormat('d F Y');
        $writer->addRow(Row::fromValues([$formattedPeriod], $this->getMetaStyle()));

        $filterInfo = 'Kategori: ' . ($kategori ?: 'Semua Kategori');
        $writer->addRow(Row::fromValues([$filterInfo], $this->getMetaStyle()));
        $writer->addRow(Row::fromValues([''])); // Blank row

        // Table Column Headers
        $writer->addRow(Row::fromValues([
            'No.',
            'Tanggal',
            'No. Bukti',
            'Jenis Voucher',
            'Kode Akun',
            'Nama Akun / Uraian Pos',
            'Kategori',
            'Keterangan Transaksi',
            'Pihak Terkait',
            'Nominal (Rp)',
        ], $this->getTableHeaderStyle()));

        $totalNominal = 0;
        $categoryTotals = [];
        $no = 1;

        foreach ($reportData as $tx) {
            $voucher = $tx->voucher;
            $coa = $tx->chartOfAccount;

            $tgl = $voucher?->tanggal ? Carbon::parse($voucher->tanggal)->translatedFormat('d/m/Y') : '-';
            $noBukti = $tx->no_bukti ?? '-';
            $jenis = $voucher?->jenis_voucher ?? '-';
            $kode = $tx->kode_akun ?? '-';
            $namaAkun = $coa?->nama_akun ?? '-';
            $kat = $coa?->kategori ?? '-';
            $uraian = $tx->uraian ?: ($voucher?->keterangan ?? '-');
            $pihak = $voucher?->pihak_terkait ?? '-';
            $nominal = (float) $tx->nominal;

            $totalNominal += $nominal;
            $categoryTotals[$kat] = ($categoryTotals[$kat] ?? 0) + $nominal;

            $cells = [
                Cell::fromValue($no++, $this->getDataRowStyle()),
                Cell::fromValue($tgl, $this->getDataRowStyle()),
                Cell::fromValue($noBukti, $this->getDataRowStyle()),
                Cell::fromValue($jenis, $this->getDataRowStyle()),
                Cell::fromValue($kode, $this->getDataRowStyle()),
                Cell::fromValue($namaAkun, $this->getDataRowStyle()),
                Cell::fromValue($kat, $this->getDataRowStyle()),
                Cell::fromValue($uraian, $this->getDataRowStyle()),
                Cell::fromValue($pihak, $this->getDataRowStyle()),
                Cell::fromValue($nominal, $this->getNumberStyle()),
            ];
            $writer->addRow(new Row($cells));
        }

        // Grand Total Row
        $grandCells = [
            Cell::fromValue('TOTAL SELURUH TRANSAKSI', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue($totalNominal, $this->getGrandTotalNumberStyle()),
        ];
        $writer->addRow(new Row($grandCells));

        // Rekapitulasi per Kategori
        $writer->addRow(Row::fromValues([''])); // Blank row
        $writer->addRow(Row::fromValues(['REKAPITULASI PER KATEGORI', '', ''], $this->getSubtitleStyle()));
        $writer->addRow(Row::fromValues(['Kategori', 'Jumlah Transaksi', 'Total Nominal (Rp)'], $this->getTableHeaderStyle()));

        foreach ($categoryTotals as $catName => $catAmount) {
            $writer->addRow(new Row([
                Cell::fromValue($catName, $this->getDataRowStyle()),
                Cell::fromValue('', $this->getDataRowStyle()),
                Cell::fromValue($catAmount, $this->getNumberStyle()),
            ]));
        }

        $writer->close();

        return $tempPath;
    }

    /**
     * Generate XLSX for Laporan Jurnal Umum (Double-Entry: Debet & Kredit)
     */
    public function generateJurnalUmumXlsx(
        Collection $journalEntries,
        string $startDate,
        string $endDate,
        ?string $jenisVoucher = null
    ): string {
        $tempPath = tempnam(sys_get_temp_dir(), 'jurnal_umum_') . '.xlsx';
        $options = new Options();
        $writer = new Writer($options);
        $writer->openToFile($tempPath);

        $churchName = AppSetting::get('church_name', 'GPIB JEMAAT HOSIANA');
        $churchAddress1 = AppSetting::get('church_address1', '');
        $churchAddress2 = AppSetting::get('church_address2', '');

        // Title Section
        $writer->addRow(Row::fromValues([strtoupper($churchName)], $this->getTitleStyle()));
        if ($churchAddress1 || $churchAddress2) {
            $writer->addRow(Row::fromValues([trim("{$churchAddress1} {$churchAddress2}")], $this->getMetaStyle()));
        }
        $writer->addRow(Row::fromValues(['JURNAL UMUM (GENERAL JOURNAL)'], $this->getSubtitleStyle()));

        $formattedPeriod = 'Periode: ' . Carbon::parse($startDate)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($endDate)->translatedFormat('d F Y');
        $writer->addRow(Row::fromValues([$formattedPeriod], $this->getMetaStyle()));

        if ($jenisVoucher) {
            $writer->addRow(Row::fromValues(['Jenis Voucher: ' . $jenisVoucher], $this->getMetaStyle()));
        }
        $writer->addRow(Row::fromValues([''])); // Blank row

        // Table Column Headers
        $writer->addRow(Row::fromValues([
            'Tanggal',
            'No. Bukti',
            'Jenis',
            'Pihak Terkait',
            'Keterangan / Nama Rekening & Akun',
            'Ref (Kode)',
            'Debet (Rp)',
            'Kredit (Rp)',
        ], $this->getTableHeaderStyle()));

        $totalDebet = 0;
        $totalKredit = 0;

        foreach ($journalEntries as $entry) {
            $tgl = Carbon::parse($entry['tanggal'])->format('d/m/Y');
            $noBukti = $entry['no_bukti'];
            $jenis = $entry['jenis_voucher'];
            $pihak = $entry['pihak_terkait'] ?: '-';
            $uraian = '(' . $entry['uraian'] . ')';

            $debetNominal = (float) $entry['debit']['nominal'];
            $kreditNominal = (float) $entry['kredit']['nominal'];
            $totalDebet += $debetNominal;
            $totalKredit += $kreditNominal;

            // Baris 1: Sisi Debet
            $writer->addRow(new Row([
                Cell::fromValue($tgl, $this->getDataRowStyle()),
                Cell::fromValue($noBukti, $this->getDataRowStyle()),
                Cell::fromValue($jenis, $this->getDataRowStyle()),
                Cell::fromValue($pihak, $this->getDataRowStyle()),
                Cell::fromValue($entry['debit']['nama_akun'], $this->getDataRowStyle()),
                Cell::fromValue($entry['debit']['kode_akun'], $this->getDataRowStyle()),
                Cell::fromValue($debetNominal, $this->getNumberStyle()),
                Cell::fromValue(0, $this->getNumberStyle()),
            ]));

            // Baris 2: Sisi Kredit (dengan indentasi)
            $writer->addRow(new Row([
                Cell::fromValue('', $this->getDataRowStyle()),
                Cell::fromValue('', $this->getDataRowStyle()),
                Cell::fromValue('', $this->getDataRowStyle()),
                Cell::fromValue('', $this->getDataRowStyle()),
                Cell::fromValue('    ↳ ' . $entry['kredit']['nama_akun'], $this->getDataRowStyle()),
                Cell::fromValue($entry['kredit']['kode_akun'], $this->getDataRowStyle()),
                Cell::fromValue(0, $this->getNumberStyle()),
                Cell::fromValue($kreditNominal, $this->getNumberStyle()),
            ]));

            // Baris 3: Uraian Transaksi
            $writer->addRow(new Row([
                Cell::fromValue('', $this->getDataRowStyle()),
                Cell::fromValue('', $this->getDataRowStyle()),
                Cell::fromValue('', $this->getDataRowStyle()),
                Cell::fromValue('', $this->getDataRowStyle()),
                Cell::fromValue($uraian, $this->getMetaStyle()),
                Cell::fromValue('', $this->getDataRowStyle()),
                Cell::fromValue('', $this->getDataRowStyle()),
                Cell::fromValue('', $this->getDataRowStyle()),
            ]));
        }

        // Grand Total Row
        $writer->addRow(new Row([
            Cell::fromValue('TOTAL DEBET & KREDIT', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue($totalDebet, $this->getGrandTotalNumberStyle()),
            Cell::fromValue($totalKredit, $this->getGrandTotalNumberStyle()),
        ]));

        $statusSeimbang = round($totalDebet, 2) === round($totalKredit, 2) ? 'STATUS: SEIMBANG (BALANCE)' : 'STATUS: TIDAK SEIMBANG';
        $writer->addRow(Row::fromValues([$statusSeimbang], $this->getSubtitleStyle()));

        $writer->close();

        return $tempPath;
    }

    /**
     * Generate XLSX for Laporan Realisasi Mingguan (Multi-Sheet)
     */
    public function generateRealisasiMingguanXlsx(
        array $reportData,
        string $startDate,
        string $endDate,
        ?string $mingguKe = null
    ): string {
        $tempPath = tempnam(sys_get_temp_dir(), 'realisasi_') . '.xlsx';
        $options = new Options();
        $writer = new Writer($options);
        $writer->openToFile($tempPath);

        $churchName = AppSetting::get('church_name', 'GPIB JEMAAT HOSIANA');
        $churchAddress1 = AppSetting::get('church_address1', '');
        $churchAddress2 = AppSetting::get('church_address2', '');
        $formattedPeriod = 'Periode: ' . Carbon::parse($startDate)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($endDate)->translatedFormat('d F Y') . ($mingguKe ? " (Minggu Ke-{$mingguKe})" : '');

        // ==========================================
        // SHEET 1: Ringkasan & Penerimaan
        // ==========================================
        $currentSheet = $writer->getCurrentSheet();
        $currentSheet->setName('Penerimaan & Ringkasan');

        $writer->addRow(Row::fromValues([strtoupper($churchName)], $this->getTitleStyle()));
        if ($churchAddress1 || $churchAddress2) {
            $writer->addRow(Row::fromValues([trim("{$churchAddress1} {$churchAddress2}")], $this->getMetaStyle()));
        }
        $writer->addRow(Row::fromValues(['LAPORAN REALISASI MINGGUAN - PENERIMAAN'], $this->getSubtitleStyle()));
        $writer->addRow(Row::fromValues([$formattedPeriod], $this->getMetaStyle()));
        $writer->addRow(Row::fromValues(['']));

        // Table Header
        $writer->addRow(Row::fromValues([
            'Kode Akun',
            'Uraian Pos Penerimaan',
            'Tingkat Akun',
            'Realisasi (Rp)',
        ], $this->getTableHeaderStyle()));

        $penerimaanItems = $reportData['penerimaan'] ?? [];
        foreach ($penerimaanItems as $item) {
            $indent = str_repeat('  ', (int) ($item['depth'] ?? 0));
            $isHeader = ! ($item['is_postable'] ?? true);
            $style = $isHeader ? $this->getSubtotalRowStyle() : $this->getDataRowStyle();
            $numStyle = $isHeader ? $this->getSubtotalNumberStyle() : $this->getNumberStyle();
            $amount = (float) ($item['total_amount'] ?? ($item['direct_amount'] ?? 0));

            $cells = [
                Cell::fromValue($item['kode_akun'] ?? '', $style),
                Cell::fromValue($indent . ($item['nama_akun'] ?? ''), $style),
                Cell::fromValue($isHeader ? 'Akun Induk / Header' : 'Akun Pos Transaksi', $style),
                Cell::fromValue($amount, $numStyle),
            ];
            $writer->addRow(new Row($cells));
        }

        // Total Penerimaan
        $totalPenerimaan = (float) ($reportData['totalPenerimaan'] ?? 0);
        $writer->addRow(new Row([
            Cell::fromValue('TOTAL PENERIMAAN', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue($totalPenerimaan, $this->getGrandTotalNumberStyle()),
        ]));

        // Executive Summary Box
        $writer->addRow(Row::fromValues(['']));
        $writer->addRow(Row::fromValues(['RINGKASAN EKSEKUTIF KEUANGAN MINGGU INI', ''], $this->getSubtitleStyle()));
        $writer->addRow(Row::fromValues(['Indikator Keuangan', 'Nilai Realisasi (Rp)'], $this->getTableHeaderStyle()));

        $totalPengeluaran = (float) ($reportData['totalPengeluaran'] ?? 0);
        $surplusDefisit = (float) ($reportData['surplusDefisit'] ?? 0);
        $totalSaldoAwal = (float) ($reportData['totalSaldoAwal'] ?? 0);
        $totalSaldoAkhir = (float) ($reportData['totalSaldoAkhir'] ?? 0);

        $writer->addRow(new Row([Cell::fromValue('Total Penerimaan', $this->getDataRowStyle()), Cell::fromValue($totalPenerimaan, $this->getNumberStyle())]));
        $writer->addRow(new Row([Cell::fromValue('Total Pengeluaran', $this->getDataRowStyle()), Cell::fromValue($totalPengeluaran, $this->getNumberStyle())]));
        $writer->addRow(new Row([Cell::fromValue('Surplus / (Defisit) Berjalan', $this->getSubtotalRowStyle()), Cell::fromValue($surplusDefisit, $this->getSubtotalNumberStyle())]));
        $writer->addRow(new Row([Cell::fromValue('Total Saldo Awal Kas & Bank', $this->getDataRowStyle()), Cell::fromValue($totalSaldoAwal, $this->getNumberStyle())]));
        $writer->addRow(new Row([Cell::fromValue('Total Saldo Akhir Kas & Bank', $this->getGrandTotalRowStyle()), Cell::fromValue($totalSaldoAkhir, $this->getGrandTotalNumberStyle())]));

        // ==========================================
        // SHEET 2: Pengeluaran
        // ==========================================
        $sheet2 = $writer->addNewSheetAndMakeItCurrent();
        $sheet2->setName('Pengeluaran');

        $writer->addRow(Row::fromValues([strtoupper($churchName)], $this->getTitleStyle()));
        $writer->addRow(Row::fromValues(['LAPORAN REALISASI MINGGUAN - PENGELUARAN'], $this->getSubtitleStyle()));
        $writer->addRow(Row::fromValues([$formattedPeriod], $this->getMetaStyle()));
        $writer->addRow(Row::fromValues(['']));

        $writer->addRow(Row::fromValues([
            'Kode Akun',
            'Uraian Pos Pengeluaran',
            'Tingkat Akun',
            'Realisasi (Rp)',
        ], $this->getTableHeaderStyle()));

        $pengeluaranItems = $reportData['pengeluaran'] ?? [];
        foreach ($pengeluaranItems as $item) {
            $indent = str_repeat('  ', (int) ($item['depth'] ?? 0));
            $isHeader = ! ($item['is_postable'] ?? true);
            $style = $isHeader ? $this->getSubtotalRowStyle() : $this->getDataRowStyle();
            $numStyle = $isHeader ? $this->getSubtotalNumberStyle() : $this->getNumberStyle();
            $amount = (float) ($item['total_amount'] ?? ($item['direct_amount'] ?? 0));

            $cells = [
                Cell::fromValue($item['kode_akun'] ?? '', $style),
                Cell::fromValue($indent . ($item['nama_akun'] ?? ''), $style),
                Cell::fromValue($isHeader ? 'Akun Induk / Header' : 'Akun Pos Transaksi', $style),
                Cell::fromValue($amount, $numStyle),
            ];
            $writer->addRow(new Row($cells));
        }

        $writer->addRow(new Row([
            Cell::fromValue('TOTAL PENGELUARAN', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue($totalPengeluaran, $this->getGrandTotalNumberStyle()),
        ]));

        // ==========================================
        // SHEET 3: Saldo Kas & Bank
        // ==========================================
        $sheet3 = $writer->addNewSheetAndMakeItCurrent();
        $sheet3->setName('Saldo Kas & Bank');

        $writer->addRow(Row::fromValues([strtoupper($churchName)], $this->getTitleStyle()));
        $writer->addRow(Row::fromValues(['LAPORAN MUTASI & SALDO KAS / BANK'], $this->getSubtitleStyle()));
        $writer->addRow(Row::fromValues([$formattedPeriod], $this->getMetaStyle()));
        $writer->addRow(Row::fromValues(['']));

        $writer->addRow(Row::fromValues([
            'Kode Akun',
            'Nama Akun Kas / Rekening Bank',
            'Saldo Awal (Rp)',
            'Penerimaan / Mutasi Masuk (Rp)',
            'Pengeluaran / Mutasi Keluar (Rp)',
            'Saldo Akhir (Rp)',
        ], $this->getTableHeaderStyle()));

        $kasBankItems = $reportData['kasBank'] ?? [];
        $sumAwal = 0;
        $sumMasuk = 0;
        $sumKeluar = 0;
        $sumAkhir = 0;

        foreach ($kasBankItems as $item) {
            $awal = (float) ($item['saldo_awal'] ?? 0);
            $masuk = (float) ($item['mutasi_masuk'] ?? ($item['total_masuk'] ?? 0));
            $keluar = (float) ($item['mutasi_keluar'] ?? ($item['total_keluar'] ?? 0));
            $akhir = (float) ($item['saldo_akhir'] ?? 0);

            $sumAwal += $awal;
            $sumMasuk += $masuk;
            $sumKeluar += $keluar;
            $sumAkhir += $akhir;

            $cells = [
                Cell::fromValue($item['kode_akun'] ?? '', $this->getDataRowStyle()),
                Cell::fromValue($item['nama_akun'] ?? '', $this->getDataRowStyle()),
                Cell::fromValue($awal, $this->getNumberStyle()),
                Cell::fromValue($masuk, $this->getNumberStyle()),
                Cell::fromValue($keluar, $this->getNumberStyle()),
                Cell::fromValue($akhir, $this->getNumberStyle()),
            ];
            $writer->addRow(new Row($cells));
        }

        $writer->addRow(new Row([
            Cell::fromValue('TOTAL SALDO KAS & BANK', $this->getGrandTotalRowStyle()),
            Cell::fromValue('', $this->getGrandTotalRowStyle()),
            Cell::fromValue($sumAwal, $this->getGrandTotalNumberStyle()),
            Cell::fromValue($sumMasuk, $this->getGrandTotalNumberStyle()),
            Cell::fromValue($sumKeluar, $this->getGrandTotalNumberStyle()),
            Cell::fromValue($sumAkhir, $this->getGrandTotalNumberStyle()),
        ]));

        $writer->close();

        return $tempPath;
    }
}
