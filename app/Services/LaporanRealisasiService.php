<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanRealisasiService
{
    /**
     * Generate full weekly realization report data.
     */
    public function getWeeklyReport(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // 1. Penerimaan
        $penerimaanData = $this->getCategoryHierarchyReport('Penerimaan', $start->toDateString(), $end->toDateString());

        // 2. Pengeluaran
        $pengeluaranData = $this->getCategoryHierarchyReport('Pengeluaran', $start->toDateString(), $end->toDateString());

        // 3. Saldo Kas & Bank
        $kasBankData = $this->getKasBankReport($start->toDateString(), $end->toDateString());

        // 4. Summary Totals
        $totalPenerimaan = $penerimaanData['grand_total'] ?? 0;
        $totalPengeluaran = $pengeluaranData['grand_total'] ?? 0;
        $surplusDefisit = $totalPenerimaan - $totalPengeluaran;
        $totalSaldoAwal = $kasBankData['total_saldo_awal'] ?? 0;
        $totalSaldoAkhir = $kasBankData['total_saldo_akhir'] ?? 0;

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'penerimaan' => $penerimaanData['items'] ?? [],
            'totalPenerimaan' => $totalPenerimaan,
            'pengeluaran' => $pengeluaranData['items'] ?? [],
            'totalPengeluaran' => $totalPengeluaran,
            'kasBank' => $kasBankData['items'] ?? [],
            'totalSaldoAwal' => $totalSaldoAwal,
            'totalSaldoAkhir' => $totalSaldoAkhir,
            'surplusDefisit' => $surplusDefisit,
        ];
    }

    /**
     * Get hierarchical accounts and amounts for a category (Penerimaan / Pengeluaran).
     */
    protected function getCategoryHierarchyReport(string $kategori, string $startDate, string $endDate): array
    {
        // Load all COA for this category ordered by kode_akun
        $accounts = ChartOfAccount::where('kategori', $kategori)
            ->orderBy('kode_akun')
            ->get();

        if ($accounts->isEmpty()) {
            return ['items' => [], 'grand_total' => 0];
        }

        // Fetch transaction sums grouped by kode_akun for the date range
        $transactionsSums = Transaction::query()
            ->selectRaw('kode_akun, SUM(nominal) as total_nominal')
            ->whereHas('voucher', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->whereIn('kode_akun', $accounts->pluck('kode_akun'))
            ->groupBy('kode_akun')
            ->pluck('total_nominal', 'kode_akun')
            ->toArray();

        // Build array keyed by kode_akun
        $accMap = [];
        foreach ($accounts as $acc) {
            $accMap[$acc->kode_akun] = [
                'kode_akun' => $acc->kode_akun,
                'nama_akun' => $acc->nama_akun,
                'parent_code' => $acc->parent_code,
                'is_postable' => (bool) $acc->is_postable,
                'depth' => substr_count($acc->kode_akun, '.'),
                'direct_amount' => (float) ($transactionsSums[$acc->kode_akun] ?? 0),
                'total_amount' => 0,
                'children' => [],
            ];
        }

        // Calculate recursive rollup totals
        $rootCodes = [];
        foreach ($accMap as $code => &$item) {
            $parentCode = $item['parent_code'];
            if ($parentCode && isset($accMap[$parentCode])) {
                $accMap[$parentCode]['children'][] = $code;
            } else {
                $rootCodes[] = $code;
            }
        }
        unset($item);

        // Recursive sum function
        $calculateTotal = function ($code) use (&$accMap, &$calculateTotal): float {
            $item = &$accMap[$code];
            $sum = $item['direct_amount'];
            foreach ($item['children'] as $childCode) {
                $sum += $calculateTotal($childCode);
            }
            $item['total_amount'] = $sum;
            return $sum;
        };

        $grandTotal = 0;
        foreach ($rootCodes as $rootCode) {
            $grandTotal += $calculateTotal($rootCode);
        }

        // Flatten in tree traversal order for clean hierarchical display
        $flatList = [];
        $flatten = function ($code) use (&$accMap, &$flatList, &$flatten) {
            $item = $accMap[$code];
            $flatList[] = [
                'kode_akun' => $item['kode_akun'],
                'nama_akun' => $item['nama_akun'],
                'depth' => $item['depth'],
                'is_postable' => $item['is_postable'],
                'is_group' => !empty($item['children']) || !$item['is_postable'],
                'amount' => $item['total_amount'],
            ];
            foreach ($item['children'] as $childCode) {
                $flatten($childCode);
            }
        };

        foreach ($rootCodes as $rootCode) {
            $flatten($rootCode);
        }

        return [
            'items' => $flatList,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Get Kas & Bank position report (Saldo Awal vs Saldo Akhir).
     */
    protected function getKasBankReport(string $startDate, string $endDate): array
    {
        $accounts = ChartOfAccount::whereIn('kategori', ['Kas & Bank', 'Hutang / Piutang'])
            ->orWhere('kode_akun', 'like', '1%')
            ->orWhere('kode_akun', 'like', '51.01%')
            ->orWhere('kode_akun', 'like', '52.01%')
            ->orderBy('kode_akun')
            ->get();

        if ($accounts->isEmpty()) {
            return [
                'items' => [],
                'total_saldo_awal' => 0,
                'total_saldo_akhir' => 0,
            ];
        }

        $allCodes = $accounts->pluck('kode_akun')->toArray();

        // 1. Transactions before startDate (for Saldo Awal)
        $txBefore = Transaction::query()
            ->selectRaw('COALESCE(vouchers.kode_akun_kas_bank, transactions.kode_akun) as kas_kode_akun, vouchers.jenis_voucher, SUM(transactions.nominal) as total')
            ->join('vouchers', 'vouchers.no_bukti', '=', 'transactions.no_bukti')
            ->where('vouchers.tanggal', '<', $startDate)
            ->where(function ($q) use ($allCodes) {
                $q->whereIn('vouchers.kode_akun_kas_bank', $allCodes)
                  ->orWhere(function ($q2) use ($allCodes) {
                      $q2->whereNull('vouchers.kode_akun_kas_bank')
                         ->whereIn('transactions.kode_akun', $allCodes);
                  });
            })
            ->groupBy(DB::raw('COALESCE(vouchers.kode_akun_kas_bank, transactions.kode_akun)'), 'vouchers.jenis_voucher')
            ->get();

        $saldoAwalMap = [];
        foreach ($txBefore as $row) {
            $nominal = (float) $row->total;
            $current = $saldoAwalMap[$row->kas_kode_akun] ?? 0.0;
            $isKeluar = in_array($row->jenis_voucher, ['Keluar', 'BKK', 'BBK'], true);
            $saldoAwalMap[$row->kas_kode_akun] = $current + ($isKeluar ? -$nominal : $nominal);
        }

        // 1b. Inter-account transfers before startDate (e.g. setor kas ke bank)
        $txBeforeDest = Transaction::query()
            ->selectRaw('transactions.kode_akun as kas_kode_akun, vouchers.jenis_voucher, SUM(transactions.nominal) as total')
            ->join('vouchers', 'vouchers.no_bukti', '=', 'transactions.no_bukti')
            ->where('vouchers.tanggal', '<', $startDate)
            ->whereNotNull('vouchers.kode_akun_kas_bank')
            ->whereColumn('transactions.kode_akun', '!=', 'vouchers.kode_akun_kas_bank')
            ->whereIn('transactions.kode_akun', $allCodes)
            ->groupBy('transactions.kode_akun', 'vouchers.jenis_voucher')
            ->get();

        foreach ($txBeforeDest as $row) {
            $nominal = (float) $row->total;
            $current = $saldoAwalMap[$row->kas_kode_akun] ?? 0.0;
            $isKeluar = in_array($row->jenis_voucher, ['Keluar', 'BKK', 'BBK'], true);
            $saldoAwalMap[$row->kas_kode_akun] = $current + ($isKeluar ? $nominal : -$nominal);
        }

        // 2. Transactions during period [startDate, endDate]
        $txDuring = Transaction::query()
            ->selectRaw('COALESCE(vouchers.kode_akun_kas_bank, transactions.kode_akun) as kas_kode_akun, vouchers.jenis_voucher, SUM(transactions.nominal) as total')
            ->join('vouchers', 'vouchers.no_bukti', '=', 'transactions.no_bukti')
            ->whereBetween('vouchers.tanggal', [$startDate, $endDate])
            ->where(function ($q) use ($allCodes) {
                $q->whereIn('vouchers.kode_akun_kas_bank', $allCodes)
                  ->orWhere(function ($q2) use ($allCodes) {
                      $q2->whereNull('vouchers.kode_akun_kas_bank')
                         ->whereIn('transactions.kode_akun', $allCodes);
                  });
            })
            ->groupBy(DB::raw('COALESCE(vouchers.kode_akun_kas_bank, transactions.kode_akun)'), 'vouchers.jenis_voucher')
            ->get();

        $mutasiMap = [];
        foreach ($txDuring as $row) {
            $nominal = (float) $row->total;
            $current = $mutasiMap[$row->kas_kode_akun] ?? 0.0;
            $isKeluar = in_array($row->jenis_voucher, ['Keluar', 'BKK', 'BBK'], true);
            $mutasiMap[$row->kas_kode_akun] = $current + ($isKeluar ? -$nominal : $nominal);
        }

        // 2b. Inter-account transfers during period [startDate, endDate]
        $txDuringDest = Transaction::query()
            ->selectRaw('transactions.kode_akun as kas_kode_akun, vouchers.jenis_voucher, SUM(transactions.nominal) as total')
            ->join('vouchers', 'vouchers.no_bukti', '=', 'transactions.no_bukti')
            ->whereBetween('vouchers.tanggal', [$startDate, $endDate])
            ->whereNotNull('vouchers.kode_akun_kas_bank')
            ->whereColumn('transactions.kode_akun', '!=', 'vouchers.kode_akun_kas_bank')
            ->whereIn('transactions.kode_akun', $allCodes)
            ->groupBy('transactions.kode_akun', 'vouchers.jenis_voucher')
            ->get();

        foreach ($txDuringDest as $row) {
            $nominal = (float) $row->total;
            $current = $mutasiMap[$row->kas_kode_akun] ?? 0.0;
            $isKeluar = in_array($row->jenis_voucher, ['Keluar', 'BKK', 'BBK'], true);
            $mutasiMap[$row->kas_kode_akun] = $current + ($isKeluar ? $nominal : -$nominal);
        }

        // Build account map
        $accMap = [];
        foreach ($accounts as $acc) {
            $directAwal = (float) ($saldoAwalMap[$acc->kode_akun] ?? 0);
            $directMutasi = (float) ($mutasiMap[$acc->kode_akun] ?? 0);
            $directAkhir = $directAwal + $directMutasi;

            $accMap[$acc->kode_akun] = [
                'kode_akun' => $acc->kode_akun,
                'nama_akun' => $acc->nama_akun,
                'parent_code' => $acc->parent_code,
                'is_postable' => (bool) $acc->is_postable,
                'depth' => substr_count($acc->kode_akun, '.'),
                'direct_saldo_awal' => $directAwal,
                'direct_saldo_akhir' => $directAkhir,
                'total_saldo_awal' => 0,
                'total_saldo_akhir' => 0,
                'children' => [],
            ];
        }

        $rootCodes = [];
        foreach ($accMap as $code => &$item) {
            $parentCode = $item['parent_code'];
            if ($parentCode && isset($accMap[$parentCode])) {
                $accMap[$parentCode]['children'][] = $code;
            } else {
                $rootCodes[] = $code;
            }
        }
        unset($item);

        // Recursive rollup function for balances
        $calculateBalances = function ($code) use (&$accMap, &$calculateBalances): array {
            $item = &$accMap[$code];
            $sumAwal = $item['direct_saldo_awal'];
            $sumAkhir = $item['direct_saldo_akhir'];

            foreach ($item['children'] as $childCode) {
                $childBalances = $calculateBalances($childCode);
                $sumAwal += $childBalances['awal'];
                $sumAkhir += $childBalances['akhir'];
            }

            $item['total_saldo_awal'] = $sumAwal;
            $item['total_saldo_akhir'] = $sumAkhir;

            return ['awal' => $sumAwal, 'akhir' => $sumAkhir];
        };

        $totalSaldoAwal = 0;
        $totalSaldoAkhir = 0;
        foreach ($rootCodes as $rootCode) {
            $b = $calculateBalances($rootCode);
            $totalSaldoAwal += $b['awal'];
            $totalSaldoAkhir += $b['akhir'];
        }

        // Flatten in tree order
        $flatList = [];
        $flatten = function ($code) use (&$accMap, &$flatList, &$flatten) {
            $item = $accMap[$code];
            $flatList[] = [
                'kode_akun' => $item['kode_akun'],
                'nama_akun' => $item['nama_akun'],
                'depth' => $item['depth'],
                'is_postable' => $item['is_postable'],
                'is_group' => !empty($item['children']) || !$item['is_postable'],
                'saldo_awal' => $item['total_saldo_awal'],
                'saldo_akhir' => $item['total_saldo_akhir'],
            ];
            foreach ($item['children'] as $childCode) {
                $flatten($childCode);
            }
        };

        foreach ($rootCodes as $rootCode) {
            $flatten($rootCode);
        }

        return [
            'items' => $flatList,
            'total_saldo_awal' => $totalSaldoAwal,
            'total_saldo_akhir' => $totalSaldoAkhir,
        ];
    }
}
