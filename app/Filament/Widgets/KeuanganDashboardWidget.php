<?php

namespace App\Filament\Widgets;

use App\Models\Voucher;
use App\Services\LaporanRealisasiService;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class KeuanganDashboardWidget extends Widget
{
    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.keuangan-dashboard';

    protected int|string|array $columnSpan = 'full';

    public string $rentang = 'minggu';

    public function updatedRentang(): void
    {
        if (! in_array($this->rentang, ['minggu', 'bulan', 'tahun'], true)) {
            $this->rentang = 'minggu';
        }
    }

    protected function getViewData(): array
    {
        [$start, $end] = $this->getDateRange();
        $report = app(LaporanRealisasiService::class)->getWeeklyReport($start->toDateString(), $end->toDateString());
        $vouchers = Voucher::query()
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal')
            ->get(['no_bukti', 'tanggal', 'pihak_terkait', 'jenis_voucher', 'total_nominal']);

        $chart = $this->getChartData($start, $end);
        $recentVouchers = Voucher::query()
            ->latest('tanggal')
            ->latest('created_at')
            ->limit(6)
            ->get(['no_bukti', 'tanggal', 'pihak_terkait', 'jenis_voucher', 'total_nominal']);

        return [
            'start' => $start,
            'end' => $end,
            'report' => $report,
            'chart' => $chart,
            'recentVouchers' => $recentVouchers,
            'periodLabel' => $this->getPeriodLabel($start, $end),
            'totalVoucher' => $vouchers->count(),
        ];
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function getDateRange(): array
    {
        $today = now()->startOfDay();

        return match ($this->rentang) {
            'bulan' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'tahun' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            default => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
        };
    }

    private function getPeriodLabel(Carbon $start, Carbon $end): string
    {
        return match ($this->rentang) {
            'bulan' => $start->translatedFormat('F Y'),
            'tahun' => $start->format('Y'),
            default => $start->translatedFormat('d M').' – '.$end->translatedFormat('d M Y'),
        };
    }

    /** @return array{labels: array<int, string>, masuk: array<int, float>, keluar: array<int, float>} */
    private function getChartData(Carbon $start, Carbon $end): array
    {
        $monthly = $this->rentang === 'tahun';
        $periods = [];
        $cursor = $start->copy();
        while ($cursor <= $end) {
            $key = $monthly ? $cursor->format('Y-m') : $cursor->toDateString();
            $periods[$key] = ['label' => $monthly ? $cursor->translatedFormat('M') : $cursor->translatedFormat('D'), 'masuk' => 0.0, 'keluar' => 0.0];
            $monthly ? $cursor->addMonth() : $cursor->addDay();
        }

        $transactions = DB::table('transactions')
            ->join('vouchers', 'vouchers.no_bukti', '=', 'transactions.no_bukti')
            ->join('chart_of_accounts', 'chart_of_accounts.kode_akun', '=', 'transactions.kode_akun')
            ->whereBetween('vouchers.tanggal', [$start->toDateString(), $end->toDateString()])
            ->whereIn('chart_of_accounts.kategori', ['Penerimaan', 'Pengeluaran'])
            ->selectRaw('DATE(vouchers.tanggal) as tanggal, chart_of_accounts.kategori, SUM(transactions.nominal) as total')
            ->groupBy('tanggal', 'chart_of_accounts.kategori')
            ->get();

        foreach ($transactions as $transaction) {
            $date = Carbon::parse($transaction->tanggal);
            $key = $monthly ? $date->format('Y-m') : $date->toDateString();
            if (! isset($periods[$key])) {
                continue;
            }

            $series = $transaction->kategori === 'Pengeluaran' ? 'keluar' : 'masuk';
            $periods[$key][$series] += (float) $transaction->total;
        }

        return [
            'labels' => array_column($periods, 'label'),
            'masuk' => array_column($periods, 'masuk'),
            'keluar' => array_column($periods, 'keluar'),
        ];
    }
}
