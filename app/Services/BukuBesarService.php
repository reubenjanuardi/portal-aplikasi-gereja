<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\OpeningBalance;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Pusat data Laporan Buku Besar.
 *
 * Buku Besar punya dua sumber mutasi:
 *  1. Transaksi rutin   -> `transactions.kode_akun` (mata anggaran / pos beban & penerimaan).
 *  2. Mutasi Kas & Bank -> `vouchers.kode_akun_kas_bank` (akun kas/bank sumber & tujuan dana).
 *
 * Akun seperti "Kas Kecil" (111.02) nyaris tidak pernah dipakai sebagai mata anggaran,
 * sehingga kalau hanya `transactions.kode_akun` yang dibaca, saldonya tidak akan pernah muncul.
 * Service ini menggabungkan kedua sumber agar saldo Kas Kecil / Kas Besar / Bank bisa dicek.
 */
class BukuBesarService
{
    /**
     * Kode voucher yang menandakan penerimaan dana (penerimaan = Debet di akun Kas & Bank).
     */
    public const VOUCHER_MASUK = ['Masuk', 'BKM', 'BBM'];

    /**
     * Susun data buku besar per akun: baris mutasi, saldo awal, dan saldo akhir.
     *
     * @return array{accounts: Collection<int, array<string, mixed>>}
     */
    public function getReportData(
        string $startDate,
        string $endDate,
        ?string $kodeAkun = null,
        ?string $jenisVoucher = null,
    ): array {
        $kasBankCodes = $this->kasBankCodes();
        $mataAnggaranCodes = $this->mataAnggaranCodes();

        $accounts = $this->buildAccounts($kasBankCodes);

        if ($accounts->isEmpty()) {
            return ['accounts' => collect()];
        }

        // Saldo awal dihitung dari dua sumber:
        //  1. Saldo awal yang ditetapkan pengguna pada tabel `opening_balances`.
        //  2. Mutasi yang tercatat setelah tanggal saldo awal dan sebelum periode.
        //
        // Bila saldo awal manual tersedia, mutasi pada atau sebelum tanggalnya
        // TIDAK dijumlahkan karena sudah tercakup dalam angka saldo awal.
        // Filter jenis voucher tetap diterapkan agar "saldo akhir" konsisten
        // dengan total mutasi yang benar-benar ditampilkan.
        $manualOpening = $this->fetchManualOpeningBalances($startDate);
        $mutationStart = $manualOpening->isNotEmpty()
            ? $this->dayAfter($manualOpening->first()['periode'])
            : null;

        $opening = $this->fetchMutations($mutationStart, $this->dayBefore($startDate), $jenisVoucher, $kasBankCodes, $mataAnggaranCodes);
        $during = $this->fetchMutations($startDate, $endDate, $jenisVoucher, $kasBankCodes, $mataAnggaranCodes);

        foreach ($manualOpening as $mutation) {
            $opening->push([
                'kode_akun' => $mutation['kode_akun'],
                'delta'     => $mutation['delta'],
            ]);
        }

        // Akun dikelompokkan pada array biasa agar mutasi bisa ditambahkan tanpa
        // masalah "indirect modification" yang terjadi pada Collection.
        $grouped = [];
        foreach ($accounts as $account) {
            $grouped[$account['kode_akun']] = $account;
        }

        foreach ($opening as $mutation) {
            $kode = $mutation['kode_akun'];
            if (! isset($grouped[$kode])) {
                continue;
            }

            $grouped[$kode]['saldo_awal'] += $mutation['delta'];
        }

        foreach ($during as $mutation) {
            $kode = $mutation['kode_akun'];
            if (! isset($grouped[$kode])) {
                continue;
            }

            $grouped[$kode]['lines']->push($mutation);
            $grouped[$kode]['total_masuk'] += $mutation['is_masuk'] ? $mutation['nominal'] : 0.0;
            $grouped[$kode]['total_keluar'] += $mutation['is_masuk'] ? 0.0 : $mutation['nominal'];
            $grouped[$kode]['total_debit'] += $mutation['debit'];
            $grouped[$kode]['total_kredit'] += $mutation['kredit'];
        }

        $accounts = collect($grouped)
            ->map(function (array $account): array {
                $account['lines'] = $account['lines']
                    ->sortBy(fn (array $line): string => $line['tanggal'] . '|' . $line['no_bukti'])
                    ->values();
                // Saldo normal akuntansi: akun Kas & Bank dan pos Pengeluaran
                // bersaldo Debit, akun Penerimaan bersaldo Kredit.
                $account['saldo_akhir'] = $account['saldo_awal'] + $account['total_debit'] - $account['total_kredit'];

                return $account;
            })
            // Akun ditampilkan bila punya mutasi pada periode ini ATAU punya
            // saldo awal. Akun kas/bank seperti BOTI atau deposito bisa saja
            // tidak pernah bertransaksi, tetapi saldonya tetap harus terlihat.
            ->filter(fn (array $account): bool => $account['lines']->isNotEmpty()
                || round($account['saldo_awal'], 2) !== 0.0)
            ->values();

        if ($kodeAkun) {
            $targetCodes = array_merge([$kodeAkun], $this->descendantCodes($kodeAkun));

            $accounts = $accounts->filter(
                fn (array $account): bool => in_array($account['kode_akun'], $targetCodes, true)
            )->values();
        }

        return ['accounts' => $accounts];
    }

    /**
     * Daftar akun CoA postable, saldo awal 0 (dihitung terpisah pada getReportData).
     */
    protected function buildAccounts(Collection $kasBankCodes): Collection
    {
        return ChartOfAccount::query()
            ->where('is_postable', true)
            ->orderBy('kode_akun')
            ->get()
            ->map(fn (ChartOfAccount $coa): array => [
                'kode_akun'    => $coa->kode_akun,
                'nama_akun'    => $coa->nama_akun,
                'kategori'     => $coa->kategori,
                'is_kas_bank'  => $kasBankCodes->has($coa->kode_akun),
                'lines'        => collect(),
                'saldo_awal'   => 0.0,
                'total_masuk'  => 0.0,
                'total_keluar' => 0.0,
                'total_debit'  => 0.0,
                'total_kredit' => 0.0,
                'saldo_akhir'  => 0.0,
            ])
            ->values();
    }

    /**
     * Kode akun postable bertipe Kas & Bank (dipakai di form voucher sebagai sumber/tujuan dana).
     */
    public function kasBankCodes(): Collection
    {
        return ChartOfAccount::where('kategori', 'Kas & Bank')
            ->where('is_postable', true)
            ->pluck('kode_akun')
            ->flip();
    }

    /**
     * Kode akun postable di luar Kas & Bank (dipakai di form voucher sebagai mata anggaran).
     */
    public function mataAnggaranCodes(): Collection
    {
        return ChartOfAccount::where('kategori', '!=', 'Kas & Bank')
            ->where('is_postable', true)
            ->pluck('kode_akun')
            ->flip();
    }

    /**
     * Kode akun anak (descendant) dari sebuah akun grup.
     *
     * @return array<int, string>
     */
    public function descendantCodes(string $kodeAkun): array
    {
        $descendants = [];
        $queue = [$kodeAkun];

        while ($queue !== []) {
            $current = array_shift($queue);
            $children = ChartOfAccount::where('parent_code', $current)->pluck('kode_akun')->all();

            foreach ($children as $child) {
                if (in_array($child, $descendants, true)) {
                    continue;
                }
                $descendants[] = $child;
                $queue[] = $child;
            }
        }

        return $descendants;
    }

    /**
     * Gabungkan mutasi dari dua sumber (mata anggaran + kas/bank) dalam satu rentang tanggal.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function fetchMutations(
        ?string $startDate,
        ?string $endDate,
        ?string $jenisVoucher,
        Collection $kasBankCodes,
        Collection $mataAnggaranCodes,
    ): Collection {
        $baseQuery = fn () => Transaction::query()
            ->join('vouchers', 'vouchers.no_bukti', '=', 'transactions.no_bukti')
            ->select([
                'transactions.kode_akun as kode_akun',
                'vouchers.tanggal as tanggal',
                'transactions.no_bukti as no_bukti',
                'vouchers.jenis_voucher as jenis_voucher',
                'vouchers.pihak_terkait as pihak_terkait',
                'transactions.uraian as uraian',
                'transactions.nominal as nominal',
            ])
            ->when($startDate, fn ($q) => $q->where('vouchers.tanggal', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('vouchers.tanggal', '<=', $endDate))
            ->when($jenisVoucher, fn ($q) => $q->where('vouchers.jenis_voucher', $jenisVoucher));

        // Sumber 1: mutasi mata anggaran (transaksi rutin).
        $mataAnggaranRows = $baseQuery()
            ->whereIn('transactions.kode_akun', $mataAnggaranCodes->keys()->all())
            ->get();

        // Sumber 2: mutasi akun Kas & Bank dari header voucher.
        $kasBankRows = $baseQuery()
            ->select([
                'vouchers.kode_akun_kas_bank as kode_akun',
                'vouchers.tanggal as tanggal',
                'transactions.no_bukti as no_bukti',
                'vouchers.jenis_voucher as jenis_voucher',
                'vouchers.pihak_terkait as pihak_terkait',
                'transactions.uraian as uraian',
                'transactions.nominal as nominal',
            ])
            ->whereNotNull('vouchers.kode_akun_kas_bank')
            ->whereIn('vouchers.kode_akun_kas_bank', $kasBankCodes->keys()->all())
            ->get();

        // Sumber 3: transfer antar akun Kas & Bank (mis. tarik tunai bank ke kas).
        // Satu voucher sudah mewakili dua sisi sekaligus: akun sumber di
        // `vouchers.kode_akun_kas_bank` dan akun tujuan di `transactions.kode_akun`.
        // Sisi tujuan dihitung dengan arah berlawanan supaya perpindahan uang
        // tidak terhitung dua kali.
        $transferRows = $baseQuery()
            ->select([
                'transactions.kode_akun as kode_akun',
                'vouchers.kode_akun_kas_bank as sumber_akun',
                'vouchers.tanggal as tanggal',
                'transactions.no_bukti as no_bukti',
                'vouchers.jenis_voucher as jenis_voucher',
                'vouchers.pihak_terkait as pihak_terkait',
                'transactions.uraian as uraian',
                'transactions.nominal as nominal',
            ])
            ->whereNotNull('vouchers.kode_akun_kas_bank')
            ->whereColumn('transactions.kode_akun', '!=', 'vouchers.kode_akun_kas_bank')
            ->whereIn('transactions.kode_akun', $kasBankCodes->keys()->all())
            ->get();

        return $mataAnggaranRows
            ->concat($kasBankRows)
            ->concat($transferRows)
            ->map(fn ($row): array => $this->mapMutation($row, $kasBankCodes))
            ->values();
    }

    /**
     * Normalisasi satu baris mutasi menjadi bentuk seragam untuk buku besar.
     *
     * @return array<string, mixed>
     */
    protected function mapMutation($row, Collection $kasBankCodes): array
    {
        $nominal = (float) $row->nominal;
        $isMasuk = in_array($row->jenis_voucher, self::VOUCHER_MASUK, true);
        $isKasBank = $kasBankCodes->has($row->kode_akun);

        // Sisi tujuan transfer antar kas/bank memiliki arah berlawanan dengan
        // sisi sumber pada voucher yang sama. Tanpa ini, perpindahan uang
        // akan terhitung sebagai masuk pada kedua akun sekaligus.
        $isTransferTujuan = ($row->sumber_akun ?? null) !== null
            && $row->sumber_akun !== $row->kode_akun;

        // Akun Kas & Bank: penerimaan = Debet, pengeluaran = Kredit.
        // Akun mata anggaran: kebalikannya (Penerimaan = Kredit, Pengeluaran = Debet).
        if ($isTransferTujuan) {
            $delta = $isMasuk ? -$nominal : $nominal;
            $debit = $isMasuk ? 0.0 : $nominal;
            $kredit = $isMasuk ? $nominal : 0.0;
        } elseif ($isKasBank) {
            $delta = $isMasuk ? $nominal : -$nominal;
            $debit = $isMasuk ? $nominal : 0.0;
            $kredit = $isMasuk ? 0.0 : $nominal;
        } else {
            $delta = $isMasuk ? -$nominal : $nominal;
            $debit = $isMasuk ? 0.0 : $nominal;
            $kredit = $isMasuk ? $nominal : 0.0;
        }

        return [
            'kode_akun'     => $row->kode_akun,
            'tanggal'       => Carbon::parse($row->tanggal)->toDateString(),
            'no_bukti'      => $row->no_bukti,
            'jenis_voucher' => $row->jenis_voucher,
            'pihak_terkait' => $row->pihak_terkait,
            'uraian'        => $row->uraian,
            'nominal'       => $nominal,
            'is_masuk'      => $isMasuk,
            'debit'         => $debit,
            'kredit'        => $kredit,
            'delta'         => $delta,
        ];
    }

    /**
     * Tanggal satu hari sebelum tanggal yang diberikan (WIB).
     */
    protected function dayBefore(string $date): string
    {
        return Carbon::parse($date)->subDay()->toDateString();
    }

    /**
     * Tanggal satu hari setelah tanggal yang diberikan (WIB).
     */
    protected function dayAfter(string $date): string
    {
        return Carbon::parse($date)->addDay()->toDateString();
    }

    /**
     * Ambil saldo awal manual dari tabel `opening_balances` yang berlaku
     * pada atau sebelum tanggal awal periode laporan.
     *
     * Tanggal paling awal yang tercatat diperlakukan sebagai titik awal
     * (mis. 1 Juli 2026), sedangkan tanggal-tanggal setelah itu dianggap
     * hasil tutup buku dan dijumlahkan. Dengan begitu angka yang sudah
     * tercatat sebagai mutasi transaksi tidak terhitung dua kali.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function fetchManualOpeningBalances(string $startDate): Collection
    {
        $basePeriode = OpeningBalance::query()
            ->whereDate('periode', '<=', $startDate)
            ->orderByDesc('periode')
            ->value('periode');

        if (! $basePeriode) {
            return collect();
        }

        // Periode terakhir yang berlaku sebelum tanggal laporan menjadi titik awal.
        return OpeningBalance::query()
            ->whereDate('periode', Carbon::parse($basePeriode)->toDateString())
            ->get()
            ->map(fn (OpeningBalance $ob): array => [
                'kode_akun' => $ob->kode_akun,
                'delta'     => (float) $ob->saldo_awal,
                'periode'   => Carbon::parse($basePeriode)->toDateString(),
            ])
            ->values();
    }
}
