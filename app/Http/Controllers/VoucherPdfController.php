<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Voucher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class VoucherPdfController extends Controller
{
    /**
     * Stream PDF in browser window for inline viewing/printing.
     */
    public function stream(Voucher $voucher): Response
    {
        abort_unless(auth()->user()?->can('keuangan.voucher.print'), 403, 'Anda tidak memiliki izin untuk mencetak bukti voucher.');

        \App\Models\ActivityLog::log(
            description: "Mencetak Bukti Voucher PDF [{$voucher->no_bukti}]",
            logName: 'keuangan',
            subject: $voucher
        );

        $voucher->load('transactions.chartOfAccount', 'chartOfAccount', 'akunKasBank');

        $settings = [
            'church_name'     => AppSetting::get('church_name',     'GPIB Jemaat Hosiana'),
            'church_address1' => AppSetting::get('church_address1', 'Jl. Rajawali Selatan V No. 7'),
            'church_address2' => AppSetting::get('church_address2', 'Jakarta Pusat 10772'),
        ];

        $terbilang = self::terbilang((int) round($voucher->total_nominal)) . ' Rupiah';

        $pdf = Pdf::loadView('pdf.voucher', compact('voucher', 'settings', 'terbilang'))
            ->setPaper('a5', 'portrait');

        $filename = 'Voucher-' . preg_replace('/[^A-Za-z0-9\-]/', '_', $voucher->no_bukti) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Force download PDF file directly.
     */
    public function download(Voucher $voucher): Response
    {
        $voucher->load('transactions.chartOfAccount', 'chartOfAccount', 'akunKasBank');

        $settings = [
            'church_name'     => AppSetting::get('church_name',     'GPIB Jemaat Hosiana'),
            'church_address1' => AppSetting::get('church_address1', 'Jl. Rajawali Selatan V No. 7'),
            'church_address2' => AppSetting::get('church_address2', 'Jakarta Pusat 10772'),
        ];

        $terbilang = self::terbilang((int) round($voucher->total_nominal)) . ' Rupiah';

        $pdf = Pdf::loadView('pdf.voucher', compact('voucher', 'settings', 'terbilang'))
            ->setPaper('a5', 'portrait');

        $filename = 'Voucher-' . preg_replace('/[^A-Za-z0-9\-]/', '_', $voucher->no_bukti) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Convert a positive integer to Indonesian words.
     * E.g.: 200000 → "Dua Ratus Ribu"
     */
    public static function terbilang(int $n): string
    {
        if ($n < 0) {
            return 'Minus ' . self::terbilang(abs($n));
        }

        $kata = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan',
            'Sepuluh', 'Sebelas', 'Dua Belas', 'Tiga Belas', 'Empat Belas', 'Lima Belas',
            'Enam Belas', 'Tujuh Belas', 'Delapan Belas', 'Sembilan Belas'];

        if ($n === 0)  return 'Nol';
        if ($n < 20)  return $kata[$n];
        if ($n < 100) return $kata[(int)($n / 10)] . ' Puluh' . ($n % 10 ? ' ' . $kata[$n % 10] : '');
        if ($n < 200) return 'Seratus' . ($n % 100 ? ' ' . self::terbilang($n % 100) : '');
        if ($n < 1000) return $kata[(int)($n / 100)] . ' Ratus' . ($n % 100 ? ' ' . self::terbilang($n % 100) : '');
        if ($n < 2000) return 'Seribu' . ($n % 1000 ? ' ' . self::terbilang($n % 1000) : '');
        if ($n < 1_000_000) return self::terbilang((int)($n / 1000)) . ' Ribu' . ($n % 1000 ? ' ' . self::terbilang($n % 1000) : '');
        if ($n < 1_000_000_000) return self::terbilang((int)($n / 1_000_000)) . ' Juta' . ($n % 1_000_000 ? ' ' . self::terbilang($n % 1_000_000) : '');

        return self::terbilang((int)($n / 1_000_000_000)) . ' Miliar' . ($n % 1_000_000_000 ? ' ' . self::terbilang($n % 1_000_000_000) : '');
    }
}
