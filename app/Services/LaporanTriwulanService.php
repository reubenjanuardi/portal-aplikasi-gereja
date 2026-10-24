<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Observers\ChartOfAccountsObserver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Laporan Keuangan Triwulan (tahun anggaran GPIB: April - Maret).
 *
 * Triwulan:
 *   I   = 1 April  - 30 Juni      (anggaran kumulatif 25% anggaran tahunan)
 *   II  = 1 Juli   - 30 September (50%)
 *   III = 1 Oktober- 31 Desember  (75%)
 *   IV  = 1 Januari- 31 Maret     (100%)
 *
 * Satu sumber logika untuk tampilan Filament, PDF, dan Excel.
 */
class LaporanTriwulanService
{
    /**
     * Bulan awal tiap triwulan pada tahun anggaran (indeks bulan 1-12).
     * Triwulan IV dimulai bulan Januari tahun berikutnya.
     */
    public const START_MONTHS = [
        1 => 4,
        2 => 7,
        3 => 10,
        4 => 13, // Januari tahun berikutnya (13 - 12)
    ];

    public const QUARTER_LABELS = [
        1 => 'Triwulan I',
        2 => 'Triwulan II',
        3 => 'Triwulan III',
        4 => 'Triwulan IV',
    ];

    /** Label nama bulan Indonesia tanpa tergantung locale PHP. */
    public const MONTH_LABELS = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Hitung rentang tanggal dan daftar bulan untuk satu triwulan.
     *
     * @return array{
     *     tahun: int, triwulan: int, triwulan_label: string,
     *     tahun_label: string, start_date: string, end_date: string,
     *     months: array<int, array{key: string, label: string, year: int, month: int}>,
     *     persen_anggaran: float
     * }
     */
    public function resolvePeriod(int $tahun, int $triwulan): array
    {
        $triwulan = max(1, min(4, $triwulan));

        // Offset 12 bulan dari Januari tahun anggaran (13 - 12 = bulan Januari
        // tahun berikutnya untuk Triwulan IV).
        $start = Carbon::create($tahun, 1, 1)
            ->startOfDay()
            ->addMonthsNoOverflow(self::START_MONTHS[$triwulan] - 1);
        $end = $start->copy()->addMonthsNoOverflow(3)->subDay();

        $months = [];
        for ($i = 0; $i < 3; $i++) {
            $monthDate = $start->copy()->addMonthsNoOverflow($i);
            $months[] = [
                'key' => $monthDate->format('Y-m'),
                'label' => self::MONTH_LABELS[(int) $monthDate->month],
                'year' => (int) $monthDate->year,
                'month' => (int) $monthDate->month,
            ];
        }

        return [
            'tahun' => $tahun,
            'triwulan' => $triwulan,
            'triwulan_label' => self::QUARTER_LABELS[$triwulan],
            'tahun_label' => $tahun.'–'.($tahun + 1),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'months' => $months,
            // Anggaran triwulan kumulatif: 25%, 50%, 75%, 100% anggaran tahunan.
            'persen_anggaran' => $triwulan * 0.25,
        ];
    }

    /**
     * Tahun anggaran dari satu tanggal (>= 1 April berarti tahun itu,
     * selain itu tahun sebelumnya).
     */
    public static function fiscalYearOf(string $date): int
    {
        $parsed = Carbon::parse($date);

        return $parsed->month >= 4 ? (int) $parsed->year : (int) $parsed->year - 1;
    }

    /**
     * Daftar tahun anggaran yang tersedia dari data voucher + tahun berjalan.
     *
     * @return array<int, string> map tahun => label "2026–2027" (urut turun)
     */
    public function availableFiscalYears(): array
    {
        $min = Voucher::query()->min('tanggal');
        $max = Voucher::query()->max('tanggal');

        $first = self::fiscalYearOf($min ?? now()->toDateString());
        $last = max(self::fiscalYearOf($max ?? now()->toDateString()), self::fiscalYearOf(now()->toDateString()));

        $years = [];
        for ($year = $last; $year >= $first; $year--) {
            $years[$year] = $year.'–'.($year + 1);
        }

        return $years;
    }

    /**
     * Data laporan triwulan: rekapitulasi + detail bulanan.
     *
     * @return array struktur laporan (lihat buildRekap/buildDetail)
     */
    public function getReport(int $tahun, int $triwulan): array
    {
        // Gunakan versi optimasi kalau production (PostgreSQL), fallback ke legacy untuk test (SQLite)
        if (app()->environment('testing') || config('database.default') === 'sqlite') {
            return $this->getLegacyReport($tahun, $triwulan);
        }

        return $this->getAggregatedReport($tahun, $triwulan);
    }

    /**
     * Versi legacy (Eloquent) — dipakai di test SQLite.
     */
    protected function getLegacyReport(int $tahun, int $triwulan): array
    {
        $period = $this->resolvePeriod($tahun, $triwulan);

        $rekap = [
            'penerimaan' => $this->buildCategory('Penerimaan', $period),
            'pengeluaran' => $this->buildCategory('Pengeluaran', $period),
        ];

        return [
            'period' => $period,
            'rekap' => $rekap,
            'detail' => array_merge(
                $rekap['penerimaan']['items'],
                $rekap['pengeluaran']['items'],
            ),
            'totalPenerimaan' => $rekap['penerimaan']['total_realisasi'],
            'totalPengeluaran' => $rekap['pengeluaran']['total_realisasi'],
            'surplusDefisit' => $rekap['penerimaan']['total_realisasi'] - $rekap['pengeluaran']['total_realisasi'],
        ];
    }

    /**
     * Versi cepat: satu query agregasi (Laravel Query Builder, dialect-agnostic) + cache per (TA, TW).
     * Output struktur identik dengan getLegacyReport().
     */
    protected function getAggregatedReport(int $tahun, int $triwulan): array
    {
        $period = $this->resolvePeriod($tahun, $triwulan);
        $coaVersion = ChartOfAccountsObserver::version();
        $cacheKey = "triwulan:report:{$tahun}:{$triwulan}:v{$coaVersion}:directions-v2";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($period) {
            return $this->buildFromAggregatedQuery($period);
        });
    }

    /**
     * Query agregasi tunggal via Query Builder (PostgreSQL compatible).
     */
    protected function buildFromAggregatedQuery(array $period): array
    {
        $monthKeys = array_column($period['months'], 'key');
        $anggaranTriwulan = $period['persen_anggaran'];

        // Determine date trunc expression per driver
        $driver = DB::getDriverName();
        $dateTrunc = $driver === 'pgsql'
            ? "to_char(v.tanggal, 'YYYY-MM')"
            : "strftime('%Y-%m', v.tanggal)";

        $rows = DB::table('chart_of_accounts as coa')
            ->leftJoin('transactions as t', 't.kode_akun', '=', 'coa.kode_akun')
            ->leftJoin('vouchers as v', function ($join) use ($period) {
                $join->on('v.no_bukti', '=', 't.no_bukti')
                    ->whereBetween('v.tanggal', [$period['start_date'], $period['end_date']]);
            })
            ->whereIn('coa.kategori', ['Penerimaan', 'Pengeluaran'])
            ->select(
                'coa.kode_akun',
                'coa.nama_akun',
                'coa.parent_code',
                'coa.kategori',
                'coa.budget',
                'coa.is_postable',
                DB::raw($dateTrunc . ' as bulan'),
                DB::raw("SUM(CASE WHEN v.jenis_voucher IN ('Masuk','BKM','BBM','Bukti Kas Masuk (BKM)','Bukti Bank Masuk (BBM)') THEN t.nominal ELSE 0 END) as masuk"),
                DB::raw("SUM(CASE WHEN v.jenis_voucher IN ('Keluar','BKK','BBK','Bukti Kas Keluar (BKK)','Bukti Bank Keluar (BBK)') THEN t.nominal ELSE 0 END) as keluar")
            )
            ->groupBy(
                'coa.kode_akun',
                'coa.nama_akun',
                'coa.parent_code',
                'coa.kategori',
                'coa.budget',
                'coa.is_postable',
                DB::raw($dateTrunc)
            )
            ->orderBy('coa.kode_akun')
            ->orderBy('bulan')
            ->get();

        return $this->transformAggregatedRows($rows, $period);
    }

    /**
     * Transform hasil query agregasi ke struktur laporan.
     */
    protected function transformAggregatedRows($rows, array $period): array
    {
        $monthKeys = array_column($period['months'], 'key');
        $anggaranTriwulan = $period['persen_anggaran'];

        // Kumpulkan akun unik dari rows
        $accounts = [];
        foreach ($rows as $row) {
            $code = $row->kode_akun;
            if (! isset($accounts[$code])) {
                $accounts[$code] = [
                    'kode_akun' => $code,
                    'nama_akun' => $row->nama_akun,
                    'parent_code' => $row->parent_code,
                    'kategori' => $row->kategori,
                    'depth' => substr_count($code, '.'),
                    'is_postable' => (bool) $row->is_postable,
                    'direct_budget' => (float) $row->budget,
                    'direct_realisasi' => 0.0,
                    'direct_monthly' => array_fill_keys($monthKeys, 0.0),
                    'direct_monthly_masuk' => array_fill_keys($monthKeys, 0.0),
                    'direct_monthly_keluar' => array_fill_keys($monthKeys, 0.0),
                    'total_budget' => 0.0,
                    'total_realisasi' => 0.0,
                    'monthly' => array_fill_keys($monthKeys, 0.0),
                    'monthly_masuk' => array_fill_keys($monthKeys, 0.0),
                    'monthly_keluar' => array_fill_keys($monthKeys, 0.0),
                    'children' => [],
                ];
            }
            $monthKey = (string) $row->bulan;
            if (isset($accounts[$code]['direct_monthly'][$monthKey])) {
                $masuk = (float) $row->masuk;
                $keluar = (float) $row->keluar;
                $accounts[$code]['direct_monthly_masuk'][$monthKey] = $masuk;
                $accounts[$code]['direct_monthly_keluar'][$monthKey] = $keluar;
                // Buku Besar: akun mata anggaran Penerimaan normalnya Kredit,
                // sedangkan Pengeluaran normalnya Debet. Agregasi per bulan tetap
                // menyimpan arah; nilai laporan kuartal memakai absolut net akun.
                $net = $accounts[$code]['kategori'] === 'Penerimaan'
                    ? $masuk - $keluar
                    : $keluar - $masuk;
                $accounts[$code]['direct_monthly'][$monthKey] = abs($net);
                $accounts[$code]['direct_realisasi'] += $net;
            }
        }

        foreach ($accounts as &$account) {
            $account['direct_realisasi'] = abs($account['direct_realisasi']);
        }
        unset($account);

        // Bangun hierarki
        $rootCodes = [];
        foreach ($accounts as $code => &$item) {
            $parentCode = $item['parent_code'];
            if ($parentCode && isset($accounts[$parentCode])) {
                $accounts[$parentCode]['children'][] = $code;
            } else {
                $rootCodes[] = $code;
            }
        }
        unset($item);

        // Rollup rekursif
        $rollup = function ($code) use (&$accounts, &$rollup, $monthKeys): array {
            $item = &$accounts[$code];
            $sumBudget = $item['direct_budget'];
            $sumRealisasi = $item['direct_realisasi'];
            $sumMonthly = $item['direct_monthly'];
            $sumMonthlyMasuk = $item['direct_monthly_masuk'];
            $sumMonthlyKeluar = $item['direct_monthly_keluar'];

            foreach ($item['children'] as $childCode) {
                $child = $rollup($childCode);
                $sumBudget += $child['budget'];
                $sumRealisasi += $child['realisasi'];
                foreach ($monthKeys as $key) {
                    $sumMonthly[$key] += $child['monthly'][$key];
                    $sumMonthlyMasuk[$key] += $child['monthly_masuk'][$key];
                    $sumMonthlyKeluar[$key] += $child['monthly_keluar'][$key];
                }
            }

            $item['total_budget'] = $sumBudget;
            $item['total_realisasi'] = $sumRealisasi;
            $item['monthly'] = $sumMonthly;
            $item['monthly_masuk'] = $sumMonthlyMasuk;
            $item['monthly_keluar'] = $sumMonthlyKeluar;

            return ['budget' => $sumBudget, 'realisasi' => $sumRealisasi, 'monthly' => $sumMonthly, 'monthly_masuk' => $sumMonthlyMasuk, 'monthly_keluar' => $sumMonthlyKeluar];
        };

        $totalBudget = 0.0;
        $totalRealisasi = 0.0;
        foreach ($rootCodes as $rootCode) {
            $result = $rollup($rootCode);
            $totalBudget += $result['budget'];
            $totalRealisasi += $result['realisasi'];
        }

        // Flatten per kategori — HANYA item postable (leaf) untuk total rekap agar tidak double-count
        $penerimaanItems = [];
        $pengeluaranItems = [];

        $flatten = function ($code) use (&$accounts, &$flatten, &$penerimaanItems, &$pengeluaranItems, $monthKeys, $anggaranTriwulan) {
            $item = $accounts[$code];
            $monthly = [];
            $monthlyMasuk = [];
            $monthlyKeluar = [];
            foreach ($monthKeys as $key) {
                $monthly[$key] = $item['monthly'][$key] ?? 0.0;
                $monthlyMasuk[$key] = $item['monthly_masuk'][$key] ?? 0.0;
                $monthlyKeluar[$key] = $item['monthly_keluar'][$key] ?? 0.0;
            }
            $budgetTriwulan = $item['total_budget'] * $anggaranTriwulan;
            $flatItem = [
                'kode_akun' => $item['kode_akun'],
                'nama_akun' => $item['nama_akun'],
                'depth' => $item['depth'],
                'is_postable' => $item['is_postable'],
                'is_group' => ! empty($item['children']) || ! $item['is_postable'],
                'anggaran_setahun' => $item['total_budget'],
                'anggaran_triwulan' => $budgetTriwulan,
                'realisasi' => $item['total_realisasi'],
                'persen' => $budgetTriwulan > 0 ? ($item['total_realisasi'] / $budgetTriwulan) * 100 : null,
                'monthly' => $monthly,
                'monthly_masuk' => $monthlyMasuk,
                'monthly_keluar' => $monthlyKeluar,
            ];

            if ($item['kategori'] === 'Penerimaan') {
                $penerimaanItems[] = $flatItem;
            } else {
                $pengeluaranItems[] = $flatItem;
            }

            foreach ($item['children'] as $childCode) {
                $flatten($childCode);
            }
        };

        foreach ($rootCodes as $rootCode) {
            $flatten($rootCode);
        }

        // Total rekap: HANYA sum leaf (is_postable) agar tidak double-count induk+anak
        $penerimaanLeaf = array_filter($penerimaanItems, fn($i) => $i['is_postable']);
        $pengeluaranLeaf = array_filter($pengeluaranItems, fn($i) => $i['is_postable']);

        $penerimaanAnggaranTriwulan = array_sum(array_column($penerimaanLeaf, 'anggaran_triwulan'));
        $penerimaanRealisasi = array_sum(array_column($penerimaanLeaf, 'realisasi'));
        $pengeluaranAnggaranTriwulan = array_sum(array_column($pengeluaranLeaf, 'anggaran_triwulan'));
        $pengeluaranRealisasi = array_sum(array_column($pengeluaranLeaf, 'realisasi'));

        return [
            'period' => $period,
            'rekap' => [
                'penerimaan' => [
                    'items' => $penerimaanItems,
                    'total_anggaran' => array_sum(array_column($penerimaanLeaf, 'anggaran_setahun')),
                    'total_anggaran_triwulan' => $penerimaanAnggaranTriwulan,
                    'total_realisasi' => $penerimaanRealisasi,
                    'total_persen' => $penerimaanAnggaranTriwulan > 0 ? ($penerimaanRealisasi / $penerimaanAnggaranTriwulan) * 100 : null,
                ],
                'pengeluaran' => [
                    'items' => $pengeluaranItems,
                    'total_anggaran' => array_sum(array_column($pengeluaranLeaf, 'anggaran_setahun')),
                    'total_anggaran_triwulan' => $pengeluaranAnggaranTriwulan,
                    'total_realisasi' => $pengeluaranRealisasi,
                    'total_persen' => $pengeluaranAnggaranTriwulan > 0 ? ($pengeluaranRealisasi / $pengeluaranAnggaranTriwulan) * 100 : null,
                ],
            ],
            'detail' => array_merge($penerimaanItems, $pengeluaranItems),
            'totalPenerimaan' => $penerimaanRealisasi,
            'totalPengeluaran' => $pengeluaranRealisasi,
            'surplusDefisit' => $penerimaanRealisasi - $pengeluaranRealisasi,
        ];
    }

    /**
     * Bangun hierarki akun satu kategori lengkap dengan realisasi bulanan (LEGACY - kept for reference/test).
     *
     * @param  array  $period  hasil resolvePeriod()
     * @return array{items: array, total_anggaran: float, total_anggaran_triwulan: float, total_realisasi: float}
     */
    protected function buildCategory(string $kategori, array $period): array
    {
        $accounts = ChartOfAccount::query()
            ->where('kategori', $kategori)
            ->orderBy('kode_akun')
            ->get(['kode_akun', 'nama_akun', 'parent_code', 'budget', 'is_postable']);

        $monthKeys = array_column($period['months'], 'key');

        $directMonthly = $this->monthlySums($accounts->pluck('kode_akun')->all(), $period);
        $directDirections = $this->monthlyDirectionSums($accounts->pluck('kode_akun')->all(), $period);
        $anggaranTriwulan = $period['persen_anggaran'];

        // Susun peta akun (nilai langsung + anak).
        $accMap = [];
        foreach ($accounts as $acc) {
            $directMonthlyForAccount = $directMonthly[$acc->kode_akun] ?? [];

            $accMap[$acc->kode_akun] = [
                'kode_akun' => $acc->kode_akun,
                'nama_akun' => $acc->nama_akun,
                'parent_code' => $acc->parent_code,
                'depth' => substr_count($acc->kode_akun, '.'),
                'is_postable' => (bool) $acc->is_postable,
                'direct_budget' => (float) $acc->budget,
                'direct_realisasi' => $kategori === 'Penerimaan'
                    ? array_sum($directDirections[$acc->kode_akun]['masuk'] ?? []) - array_sum($directDirections[$acc->kode_akun]['keluar'] ?? [])
                    : array_sum($directDirections[$acc->kode_akun]['keluar'] ?? []) - array_sum($directDirections[$acc->kode_akun]['masuk'] ?? []),
                'direct_monthly' => $directMonthlyForAccount,
                'direct_monthly_masuk' => $directDirections[$acc->kode_akun]['masuk'] ?? array_fill_keys($monthKeys, 0.0),
                'direct_monthly_keluar' => $directDirections[$acc->kode_akun]['keluar'] ?? array_fill_keys($monthKeys, 0.0),
                'total_budget' => 0.0,
                'total_realisasi' => 0.0,
                'monthly' => array_fill_keys($monthKeys, 0.0),
                'monthly_masuk' => array_fill_keys($monthKeys, 0.0),
                'monthly_keluar' => array_fill_keys($monthKeys, 0.0),
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

        // Rollup rekursif: induk = dirinya sendiri + seluruh anak (tanpa ganda).
        $rollup = function ($code) use (&$accMap, &$rollup, $monthKeys): array {
            $item = &$accMap[$code];

            $sumBudget = $item['direct_budget'];
            $sumRealisasi = $item['direct_realisasi'];
            $sumMonthly = array_fill_keys($monthKeys, 0.0);
            $sumMonthlyMasuk = array_fill_keys($monthKeys, 0.0);
            $sumMonthlyKeluar = array_fill_keys($monthKeys, 0.0);
            foreach ($item['direct_monthly'] as $key => $value) {
                if (array_key_exists($key, $sumMonthly)) {
                    $sumMonthly[$key] = $value;
                }
            }
            foreach ($item['direct_monthly_masuk'] as $key => $value) {
                if (array_key_exists($key, $sumMonthlyMasuk)) {
                    $sumMonthlyMasuk[$key] = $value;
                }
            }
            foreach ($item['direct_monthly_keluar'] as $key => $value) {
                if (array_key_exists($key, $sumMonthlyKeluar)) {
                    $sumMonthlyKeluar[$key] = $value;
                }
            }

            foreach ($item['children'] as $childCode) {
                $child = $rollup($childCode);
                $sumBudget += $child['budget'];
                $sumRealisasi += $child['realisasi'];
                foreach ($child['monthly'] as $key => $value) {
                    $sumMonthly[$key] += $value;
                }
                foreach ($child['monthly_masuk'] as $key => $value) {
                    $sumMonthlyMasuk[$key] += $value;
                }
                foreach ($child['monthly_keluar'] as $key => $value) {
                    $sumMonthlyKeluar[$key] += $value;
                }
            }

            $item['total_budget'] = $sumBudget;
            $item['total_realisasi'] = $sumRealisasi;
            $item['monthly'] = $sumMonthly;
            $item['monthly_masuk'] = $sumMonthlyMasuk;
            $item['monthly_keluar'] = $sumMonthlyKeluar;

            return ['budget' => $sumBudget, 'realisasi' => $sumRealisasi, 'monthly' => $sumMonthly, 'monthly_masuk' => $sumMonthlyMasuk, 'monthly_keluar' => $sumMonthlyKeluar];
        };

        $totalBudget = 0.0;
        $totalRealisasi = 0.0;
        foreach ($rootCodes as $rootCode) {
            $result = $rollup($rootCode);
            $totalBudget += $result['budget'];
            $totalRealisasi += $result['realisasi'];
        }

        // Flatten berurutan (pre-order) untuk tampilan hierarkis.
        $flat = [];
        $flatten = function ($code) use (&$accMap, &$flat, &$flatten, $monthKeys, $anggaranTriwulan) {
            $item = $accMap[$code];
            $monthly = [];
            $monthlyMasuk = [];
            $monthlyKeluar = [];
            foreach ($monthKeys as $key) {
                $monthly[$key] = $item['monthly'][$key] ?? 0.0;
                $monthlyMasuk[$key] = $item['monthly_masuk'][$key] ?? 0.0;
                $monthlyKeluar[$key] = $item['monthly_keluar'][$key] ?? 0.0;
            }

            $budgetTriwulan = $item['total_budget'] * $anggaranTriwulan;
            $flat[] = [
                'kode_akun' => $item['kode_akun'],
                'nama_akun' => $item['nama_akun'],
                'depth' => $item['depth'],
                'is_postable' => $item['is_postable'],
                'is_group' => ! empty($item['children']) || ! $item['is_postable'],
                'anggaran_setahun' => $item['total_budget'],
                'anggaran_triwulan' => $budgetTriwulan,
                'realisasi' => $item['total_realisasi'],
                // Real to Plan = realisasi kumulatif triwulan terhadap anggaran
                // kumulatif triwulan (25%/50%/75%/100% anggaran tahunan).
                'persen' => $budgetTriwulan > 0 ? ($item['total_realisasi'] / $budgetTriwulan) * 100 : null,
                'monthly' => $monthly,
                'monthly_masuk' => $monthlyMasuk,
                'monthly_keluar' => $monthlyKeluar,
            ];

            foreach ($item['children'] as $childCode) {
                $flatten($childCode);
            }
        };

        foreach ($rootCodes as $rootCode) {
            $flatten($rootCode);
        }

        $totalBudgetTriwulan = $totalBudget * $anggaranTriwulan;

        return [
            'items' => $flat,
            'total_anggaran' => $totalBudget,
            'total_anggaran_triwulan' => $totalBudgetTriwulan,
            'total_realisasi' => $totalRealisasi,
            'total_persen' => $totalBudgetTriwulan > 0 ? ($totalRealisasi / $totalBudgetTriwulan) * 100 : null,
        ];
    }

    /**
     * Jumlah nominal transaksi per akun per bulan dalam periode.
     *
     * @param  array<int, string>  $codes
     * @param  array  $period  hasil resolvePeriod()
     * @return array<string, array<string, float>> [kode_akun => ['Y-m' => nominal]]
     */
    protected function monthlySums(array $codes, array $period): array
    {
        if ($codes === []) {
            return [];
        }

        $rows = Transaction::query()
            ->join('vouchers', 'vouchers.no_bukti', '=', 'transactions.no_bukti')
            ->whereBetween('vouchers.tanggal', [$period['start_date'], $period['end_date']])
            ->whereIn('transactions.kode_akun', $codes)
            ->get([
                'transactions.kode_akun as kode_akun',
                'transactions.nominal as nominal',
                'vouchers.tanggal as tanggal',
            ]);

        $validKeys = array_flip(array_column($period['months'], 'key'));

        $result = [];
        foreach ($rows as $row) {
            $monthKey = Carbon::parse($row->tanggal)->format('Y-m');
            if (! isset($validKeys[$monthKey])) {
                continue;
            }
            $result[$row->kode_akun][$monthKey] = ($result[$row->kode_akun][$monthKey] ?? 0) + (float) $row->nominal;
        }

        return $result;
    }

    /**
     * Hitung monthly masuk/keluar per akun (memisahkan arah berdasarkan jenis voucher).
     *
     * @param  array<string>  $codes
     * @param  array  $period
     * @return array<string, array{masuk: array<string, float>, keluar: array<string, float>}>
     */
    protected function monthlyDirectionSums(array $codes, array $period): array
    {
        if ($codes === []) {
            return [];
        }

        $rows = Transaction::query()
            ->join('vouchers', 'vouchers.no_bukti', '=', 'transactions.no_bukti')
            ->join('chart_of_accounts', 'chart_of_accounts.kode_akun', '=', 'transactions.kode_akun')
            ->whereBetween('vouchers.tanggal', [$period['start_date'], $period['end_date']])
            ->whereIn('transactions.kode_akun', $codes)
            ->get([
                'transactions.kode_akun as kode_akun',
                'transactions.nominal as nominal',
                'vouchers.tanggal as tanggal',
                'vouchers.jenis_voucher as jenis_voucher',
                'chart_of_accounts.kategori as kategori',
            ]);

        $monthKeys = array_column($period['months'], 'key');
        $validKeys = array_flip($monthKeys);
        $result = [];

        foreach ($rows as $row) {
            $monthKey = Carbon::parse($row->tanggal)->format('Y-m');
            if (! isset($validKeys[$monthKey])) {
                continue;
            }

            $kode = $row->kode_akun;
            $jenis = $row->jenis_voucher ?? '';
            $nominal = (float) $row->nominal;
            $isMasuk = in_array($jenis, ['Masuk', 'BKM', 'BBM', 'Bukti Kas Masuk (BKM)', 'Bukti Bank Masuk (BBM)'], true);
            $isKeluar = in_array($jenis, ['Keluar', 'BKK', 'BBK', 'Bukti Kas Keluar (BKK)', 'Bukti Bank Keluar (BBK)'], true);

            if (! isset($result[$kode])) {
                $result[$kode] = [
                    'masuk' => array_fill_keys($monthKeys, 0.0),
                    'keluar' => array_fill_keys($monthKeys, 0.0),
                ];
            }

            if ($isMasuk) {
                $result[$kode]['masuk'][$monthKey] += $nominal;
            } elseif ($isKeluar) {
                $result[$kode]['keluar'][$monthKey] += $nominal;
            } elseif ($row->kategori === 'Penerimaan') {
                $result[$kode]['keluar'][$monthKey] += $nominal;
            } else {
                $result[$kode]['masuk'][$monthKey] += $nominal;
            }
        }

        return $result;
    }
}
