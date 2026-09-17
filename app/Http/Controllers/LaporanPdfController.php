<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LaporanPdfController extends Controller
{
    /**
     * Stream PDF for Laporan Buku Besar
     */
    public function bukuBesar(Request $request): Response
    {
        abort_unless(auth()->user()?->can('keuangan.laporan.export'), 403, 'Anda tidak memiliki izin untuk mengunduh laporan keuangan.');

        \App\Models\ActivityLog::log(
            description: 'Mengunduh Laporan Buku Besar PDF',
            logName: 'keuangan',
            properties: ['params' => $request->query()]
        );

        $startDate = $request->query('startDate') ?: now()->startOfMonth()->toDateString();
        $endDate = $request->query('endDate') ?: now()->endOfMonth()->toDateString();
        $kodeAkun = $request->query('kodeAkun');
        $jenisVoucher = $request->query('jenisVoucher');

        $reportData = Transaction::query()
            ->with(['chartOfAccount', 'voucher'])
            ->whereHas('voucher', function ($q) use ($startDate, $endDate, $jenisVoucher) {
                if ($startDate) {
                    $q->where('tanggal', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('tanggal', '<=', $endDate);
                }
                if ($jenisVoucher) {
                    $q->where('jenis_voucher', $jenisVoucher);
                }
            })
            ->when($kodeAkun, fn ($q) => $q->where('kode_akun', $kodeAkun))
            ->get()
            ->groupBy('kode_akun');

        $churchName = \App\Models\AppSetting::get('church_name', 'GPIB JEMAAT HOSIANA');
        $churchAddress1 = \App\Models\AppSetting::get('church_address1', '');
        $churchAddress2 = \App\Models\AppSetting::get('church_address2', '');

        $pdf = Pdf::loadView('pdf.laporan-buku-besar', compact(
            'reportData',
            'startDate',
            'endDate',
            'kodeAkun',
            'jenisVoucher',
            'churchName',
            'churchAddress1',
            'churchAddress2'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan-Buku-Besar.pdf');
    }

    /**
     * Stream PDF for Laporan Jurnal
     */
    public function jurnal(Request $request): Response
    {
        abort_unless(auth()->user()?->can('keuangan.laporan.export'), 403, 'Anda tidak memiliki izin untuk mengunduh laporan keuangan.');

        \App\Models\ActivityLog::log(
            description: 'Mengunduh Laporan Jurnal Transaksi PDF',
            logName: 'keuangan',
            properties: ['params' => $request->query()]
        );

        $startDate = $request->query('startDate') ?: now()->startOfMonth()->toDateString();
        $endDate = $request->query('endDate') ?: now()->endOfMonth()->toDateString();
        $kategori = $request->query('kategori');

        $churchName = \App\Models\AppSetting::get('church_name', 'GPIB JEMAAT HOSIANA');
        $churchAddress1 = \App\Models\AppSetting::get('church_address1', '');
        $churchAddress2 = \App\Models\AppSetting::get('church_address2', '');

        $query = Transaction::query()
            ->with(['chartOfAccount.parent', 'voucher'])
            ->whereHas('voucher', function ($q) use ($startDate, $endDate) {
                if ($startDate) {
                    $q->where('tanggal', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('tanggal', '<=', $endDate);
                }
            });

        if ($kategori) {
            $query->whereHas('chartOfAccount', function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            });
        }

        $reportData = $query->orderBy(
            \App\Models\Voucher::select('tanggal')
                ->whereColumn('vouchers.no_bukti', 'transactions.no_bukti')
        )->get();

        $pdf = Pdf::loadView('pdf.laporan-jurnal', compact(
            'reportData',
            'startDate',
            'endDate',
            'kategori',
            'churchName',
            'churchAddress1',
            'churchAddress2'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan-Jurnal.pdf');
    }

    /**
     * Stream PDF for Laporan Jurnal Umum (Double-Entry)
     */
    public function jurnalUmum(Request $request): Response
    {
        abort_unless(auth()->user()?->can('keuangan.laporan.export'), 403, 'Anda tidak memiliki izin untuk mengunduh laporan keuangan.');

        \App\Models\ActivityLog::log(
            description: 'Mengunduh Laporan Jurnal Umum PDF',
            logName: 'keuangan',
            properties: ['params' => $request->query()]
        );

        $startDate = $request->query('startDate') ?: now()->startOfMonth()->toDateString();
        $endDate = $request->query('endDate') ?: now()->endOfMonth()->toDateString();
        $jenisVoucher = $request->query('jenisVoucher');
        $search = $request->query('search');

        $churchName = \App\Models\AppSetting::get('church_name', 'GPIB JEMAAT HOSIANA');
        $churchAddress1 = \App\Models\AppSetting::get('church_address1', '');
        $churchAddress2 = \App\Models\AppSetting::get('church_address2', '');

        $query = \App\Models\Transaction::query()
            ->with(['chartOfAccount', 'voucher.akunKasBank'])
            ->whereHas('voucher', function ($q) use ($startDate, $endDate, $jenisVoucher, $search) {
                if ($startDate) {
                    $q->where('tanggal', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('tanggal', '<=', $endDate);
                }
                if ($jenisVoucher) {
                    $q->where('jenis_voucher', $jenisVoucher);
                }
                if ($search) {
                    $term = '%' . $search . '%';
                    $q->where(function ($sub) use ($term) {
                        $sub->where('no_bukti', 'like', $term)
                            ->orWhere('pihak_terkait', 'like', $term);
                    });
                }
            });

        if ($search) {
            $term = '%' . $search . '%';
            $query->orWhere('uraian', 'like', $term);
        }

        $transactions = $query->orderBy(
            \App\Models\Voucher::select('tanggal')
                ->whereColumn('vouchers.no_bukti', 'transactions.no_bukti')
        )->get();

        $journalEntries = $transactions->map(function (\App\Models\Transaction $tx) {
            $voucher = $tx->voucher;
            $isKeluar = in_array($voucher?->jenis_voucher ?? '', ['Keluar', 'BKK', 'BBK'], true);
            $nominal = (float) $tx->nominal;

            $akunKasBank = $voucher?->akunKasBank;
            $akunMataAnggaran = $tx->chartOfAccount;

            if ($isKeluar) {
                $debit = [
                    'kode_akun' => $tx->kode_akun ?? '-',
                    'nama_akun' => $akunMataAnggaran?->nama_akun ?? 'Pos Anggaran',
                    'nominal'   => $nominal,
                ];
                $kredit = [
                    'kode_akun' => $voucher?->kode_akun_kas_bank ?? '-',
                    'nama_akun' => $akunKasBank?->nama_akun ?? 'Akun Kas / Bank',
                    'nominal'   => $nominal,
                ];
            } else {
                $debit = [
                    'kode_akun' => $voucher?->kode_akun_kas_bank ?? '-',
                    'nama_akun' => $akunKasBank?->nama_akun ?? 'Akun Kas / Bank',
                    'nominal'   => $nominal,
                ];
                $kredit = [
                    'kode_akun' => $tx->kode_akun ?? '-',
                    'nama_akun' => $akunMataAnggaran?->nama_akun ?? 'Pos Anggaran',
                    'nominal'   => $nominal,
                ];
            }

            return [
                'id'            => $tx->id,
                'no_bukti'      => $tx->no_bukti,
                'tanggal'       => $voucher?->tanggal ?? now()->toDateString(),
                'jenis_voucher' => $voucher?->jenis_voucher ?? '-',
                'pihak_terkait' => $voucher?->pihak_terkait ?? '-',
                'uraian'        => $tx->uraian,
                'nominal'       => $nominal,
                'debit'         => $debit,
                'kredit'        => $kredit,
            ];
        });

        $pdf = Pdf::loadView('pdf.laporan-jurnal-umum', compact(
            'journalEntries',
            'startDate',
            'endDate',
            'jenisVoucher',
            'churchName',
            'churchAddress1',
            'churchAddress2'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan-Jurnal-Umum.pdf');
    }

    /**
     * Stream PDF for Laporan Realisasi Mingguan
     */
    public function realisasiMingguan(Request $request): Response
    {
        abort_unless(auth()->user()?->can('keuangan.laporan.export'), 403, 'Anda tidak memiliki izin untuk mengunduh laporan keuangan.');

        \App\Models\ActivityLog::log(
            description: 'Mengunduh Laporan Realisasi Mingguan PDF',
            logName: 'keuangan',
            properties: ['params' => $request->query()]
        );

        $startDate = $request->query('startDate') ?: now()->startOfWeek()->toDateString();
        $endDate = $request->query('endDate') ?: now()->endOfWeek()->toDateString();
        $mingguKe = $request->query('mingguKe') ?: '';

        $churchName = \App\Models\AppSetting::get('church_name', 'GPIB JEMAAT HOSIANA');
        $churchAddress1 = \App\Models\AppSetting::get('church_address1', '');
        $churchAddress2 = \App\Models\AppSetting::get('church_address2', '');

        $reportData = app(\App\Services\LaporanRealisasiService::class)->getWeeklyReport($startDate, $endDate);

        $pdf = Pdf::loadView('pdf.laporan-realisasi-mingguan', compact(
            'reportData',
            'startDate',
            'endDate',
            'mingguKe',
            'churchName',
            'churchAddress1',
            'churchAddress2'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan-Realisasi-Mingguan.pdf');
    }
}
