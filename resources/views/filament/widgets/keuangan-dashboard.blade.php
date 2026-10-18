@php
    use App\Support\Format;

    $saldoKasBank = (float) ($report['totalSaldoAkhir'] ?? 0);
    $totalMasuk = (float) ($report['totalPenerimaan'] ?? 0);
    $totalKeluar = (float) ($report['totalPengeluaran'] ?? 0);
    $surplusDefisit = (float) ($report['surplusDefisit'] ?? 0);
    $maxValue = max(1, ...$chart['masuk'], ...$chart['keluar']);
    $chartWidth = 900;
    $chartHeight = 220;
    $chartTop = 16;
    // Nol seri digambar sedikit di atas garis sumbu (y=180) supaya seri
    // dengan nilai nol terbaca sebagai data, bukan menutupi garis sumbu.
    $chartBottom = 170;
    $pointCount = max(1, count($chart['labels']) - 1);
    $pointsFor = function (array $values) use ($maxValue, $chartWidth, $chartHeight, $chartTop, $chartBottom, $pointCount): string {
        return collect($values)->map(function ($value, $index) use ($maxValue, $chartWidth, $chartTop, $chartBottom, $pointCount) {
            $x = 20 + ($index / $pointCount) * ($chartWidth - 40);
            $y = $chartBottom - (((float) $value / $maxValue) * ($chartBottom - $chartTop));
            return round($x, 1) . ',' . round($y, 1);
        })->implode(' ');
    };
@endphp

<div class="kd-page space-y-6">
    <div class="kd-border flex flex-col gap-4 border-b pb-5 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <h2 class="kd-heading text-xl font-semibold tracking-tight sm:text-2xl">Ringkasan Keuangan</h2>
            <p class="kd-muted mt-1 text-sm">Posisi kas, arus dana, dan voucher tercatat.</p>
        </div>
        <label class="kd-body flex shrink-0 items-center gap-3 text-sm font-medium">
            <span>Rentang</span>
            <select wire:model.live="rentang" class="kd-border rounded-lg border bg-transparent py-2 pl-3 pr-8 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[color:var(--kd-series-in)] focus:ring-offset-0">
                <option value="minggu">Minggu ini</option>
                <option value="bulan">Bulan ini</option>
                <option value="tahun">Tahun ini</option>
            </select>
        </label>
    </div>

    <div class="kd-border grid grid-cols-1 gap-x-6 gap-y-5 border-b pb-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="kd-accent min-w-0 border-l-2 pl-4">
            <p class="kd-muted text-sm">Saldo kas &amp; bank</p>
            <p class="kd-heading kd-amount mt-2 text-xl font-semibold sm:text-2xl">Rp {{ Format::nominal($saldoKasBank) }}</p>
            <p class="kd-muted mt-1 text-xs">Saldo akhir periode</p>
        </div>
        <div class="kd-divider min-w-0 border-l pl-4">
            <p class="kd-muted text-sm">Penerimaan</p>
            <p class="kd-heading kd-amount mt-2 text-lg font-semibold sm:text-xl">Rp {{ Format::nominal($totalMasuk) }}</p>
            <p class="kd-muted mt-1 text-xs">{{ $periodLabel }}</p>
        </div>
        <div class="kd-divider min-w-0 border-l pl-4">
            <p class="kd-muted text-sm">Pengeluaran</p>
            <p class="kd-heading kd-amount mt-2 text-lg font-semibold sm:text-xl">Rp {{ Format::nominal($totalKeluar) }}</p>
            <p class="kd-muted mt-1 text-xs">{{ $periodLabel }}</p>
        </div>
        <div class="kd-divider min-w-0 border-l pl-4">
            <p class="kd-muted text-sm">Surplus / defisit</p>
            <p class="kd-heading kd-amount mt-2 text-lg font-semibold sm:text-xl {{ $surplusDefisit < 0 ? 'kd-negative' : '' }}">Rp {{ Format::nominal($surplusDefisit) }}</p>
            <p class="kd-muted mt-1 text-xs">Penerimaan dikurangi pengeluaran</p>
        </div>
    </div>

    <section class="kd-card kd-border rounded-xl border p-5 shadow-sm sm:p-6" aria-labelledby="arus-dana-title">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <h3 id="arus-dana-title" class="kd-heading text-base font-semibold">Arus penerimaan &amp; pengeluaran</h3>
                <p class="kd-muted mt-1 text-sm">{{ $periodLabel }} · berdasarkan tanggal voucher</p>
            </div>
            <div class="kd-muted flex shrink-0 gap-4 text-xs">
                <span class="inline-flex items-center gap-2"><span class="kd-dot-in h-2 w-2 rounded-full"></span>Penerimaan</span>
                <span class="inline-flex items-center gap-2"><span class="kd-dot-out h-2 w-2 rounded-full"></span>Pengeluaran</span>
            </div>
        </div>
        @if ($totalVoucher > 0)
            <div class="mt-5">
                <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" role="img" aria-label="Grafik penerimaan dan pengeluaran {{ $periodLabel }}" class="block h-auto w-full">
                    <line x1="20" y1="180" x2="880" y2="180" class="kd-grid-line" stroke-width="1" />
                    <line x1="20" y1="98" x2="880" y2="98" class="kd-grid-line" stroke-width="1" stroke-dasharray="3 5" />
                    <polyline points="{{ $pointsFor($chart['masuk']) }}" fill="none" class="kd-line-in" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    <polyline points="{{ $pointsFor($chart['keluar']) }}" fill="none" class="kd-line-out" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    @foreach ($chart['labels'] as $index => $label)
                        @php $x = 20 + ($index / $pointCount) * ($chartWidth - 40); @endphp
                        <text x="{{ $x }}" y="207" text-anchor="middle" class="kd-axis-label">{{ $label }}</text>
                    @endforeach
                </svg>
            </div>
        @else
            <div class="kd-border mt-5 border-t py-10 text-center">
                <p class="kd-heading font-medium">Belum ada voucher pada rentang ini</p>
                <p class="kd-muted mt-1 text-sm">Grafik akan tampil setelah transaksi tercatat.</p>
            </div>
        @endif
    </section>

    <section class="kd-card kd-border overflow-hidden rounded-xl border" aria-labelledby="voucher-terbaru-title">
        <div class="kd-border flex items-center justify-between gap-4 border-b px-5 py-4 sm:px-6">
            <div class="min-w-0">
                <h3 id="voucher-terbaru-title" class="kd-heading font-semibold">Voucher terbaru</h3>
                <p class="kd-muted mt-1 text-sm">Transaksi terakhir yang tercatat</p>
            </div>
            <a href="{{ \App\Filament\Resources\VoucherResource::getUrl() }}" class="kd-link shrink-0 text-sm font-medium">Lihat semua</a>
        </div>
        @if ($recentVouchers->isEmpty())
            <div class="kd-muted px-5 py-8 text-center text-sm">Belum ada voucher. Buat voucher pertama untuk mulai mencatat transaksi.</div>
        @else
            <div class="overflow-x-auto">
                <table class="kd-table">
                    <thead class="kd-thead">
                        <tr><th>Tanggal</th><th>Nomor bukti</th><th>Pihak terkait</th><th class="text-right">Jenis</th><th class="text-right">Nominal</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($recentVouchers as $voucher)
                            @php $isKeluar = in_array($voucher->jenis_voucher, ['Keluar', 'BKK', 'BBK'], true); @endphp
                            <tr>
                                <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($voucher->tanggal)->translatedFormat('d M Y') }}</td>
                                <td class="kd-heading whitespace-nowrap font-bold">{{ $voucher->no_bukti }}</td>
                                <td class="max-w-64 truncate">{{ $voucher->pihak_terkait ?: '—' }}</td>
                                <td class="whitespace-nowrap text-right">{{ $isKeluar ? 'Pengeluaran' : 'Penerimaan' }}</td>
                                <td class="kd-amount whitespace-nowrap text-right font-medium">Rp {{ Format::nominal($voucher->total_nominal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <p class="kd-muted text-xs">Ringkasan dihitung dari voucher dan saldo awal yang tercatat. Periode: {{ $start->translatedFormat('d M Y') }} – {{ $end->translatedFormat('d M Y') }}.</p>
</div>
