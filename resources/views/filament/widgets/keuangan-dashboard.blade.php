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

<div class="kd-page">
    <header class="kd-header">
        <div class="kd-header-main">
            <h2 class="kd-title">Ringkasan Keuangan</h2>
            <p class="kd-subtitle">Posisi kas, arus dana, dan voucher tercatat.</p>
        </div>
        <label class="kd-range">
            <span>Rentang</span>
            <select wire:model.live="rentang" class="kd-select">
                <option value="minggu">Minggu ini</option>
                <option value="bulan">Bulan ini</option>
                <option value="tahun">Tahun ini</option>
            </select>
        </label>
    </header>

    <div class="kd-metrics">
        <div class="kd-metric kd-metric--saldo">
            <p class="kd-metric-label">Saldo kas &amp; bank</p>
            <p class="kd-metric-value">Rp {{ Format::nominal($saldoKasBank) }}</p>
            <p class="kd-metric-hint">Saldo akhir periode</p>
        </div>
        <div class="kd-metric kd-metric--masuk">
            <p class="kd-metric-label">Penerimaan</p>
            <p class="kd-metric-value">Rp {{ Format::nominal($totalMasuk) }}</p>
            <p class="kd-metric-hint">{{ $periodLabel }}</p>
        </div>
        <div class="kd-metric kd-metric--keluar">
            <p class="kd-metric-label">Pengeluaran</p>
            <p class="kd-metric-value">Rp {{ Format::nominal($totalKeluar) }}</p>
            <p class="kd-metric-hint">{{ $periodLabel }}</p>
        </div>
        <div class="kd-metric {{ $surplusDefisit < 0 ? 'kd-metric--defisit' : '' }}">
            <p class="kd-metric-label">Surplus / defisit</p>
            <p class="kd-metric-value">Rp {{ Format::nominal($surplusDefisit) }}</p>
            <p class="kd-metric-hint">Penerimaan dikurangi pengeluaran</p>
        </div>
    </div>

    <section class="kd-section kd-section--padded" aria-labelledby="arus-dana-title">
        <div class="kd-section-head">
            <div class="kd-header-main">
                <h3 id="arus-dana-title" class="kd-section-title">Arus penerimaan &amp; pengeluaran</h3>
                <p class="kd-section-subtitle">{{ $periodLabel }} · berdasarkan tanggal voucher</p>
            </div>
            <div class="kd-legend">
                <span class="kd-legend-item"><span class="kd-dot kd-dot-in"></span>Penerimaan</span>
                <span class="kd-legend-item"><span class="kd-dot kd-dot-out"></span>Pengeluaran</span>
            </div>
        </div>
        @if ($totalVoucher > 0)
            <div class="kd-chart-wrap">
                <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" role="img" aria-label="Grafik penerimaan dan pengeluaran {{ $periodLabel }}" class="kd-chart">
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
            <div class="kd-empty">
                <p class="kd-empty-title">Belum ada voucher pada rentang ini</p>
                <p class="kd-empty-hint">Grafik akan tampil setelah transaksi tercatat.</p>
            </div>
        @endif
    </section>

    <section class="kd-section" aria-labelledby="voucher-terbaru-title">
        <header class="kd-section-head kd-section-head--border">
            <div class="kd-header-main">
                <h3 id="voucher-terbaru-title" class="kd-section-title">Voucher terbaru</h3>
                <p class="kd-section-subtitle">Transaksi terakhir yang tercatat</p>
            </div>
            <a href="{{ \App\Filament\Resources\VoucherResource::getUrl() }}" class="kd-link">Lihat semua</a>
        </header>
        @if ($recentVouchers->isEmpty())
            <p class="kd-empty kd-empty--inline">Belum ada voucher. Buat voucher pertama untuk mulai mencatat transaksi.</p>
        @else
            <div class="kd-table-wrap">
                <table class="kd-table">
                    <thead class="kd-thead">
                        <tr><th>Tanggal</th><th>Nomor bukti</th><th>Pihak terkait</th><th class="kd-num">Jenis</th><th class="kd-num">Nominal</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($recentVouchers as $voucher)
                            @php $isKeluar = in_array($voucher->jenis_voucher, ['Keluar', 'BKK', 'BBK'], true); @endphp
                            <tr>
                                <td class="kd-nowrap">{{ \Carbon\Carbon::parse($voucher->tanggal)->translatedFormat('d M Y') }}</td>
                                <td class="kd-heading kd-nowrap kd-strong">{{ $voucher->no_bukti }}</td>
                                <td class="kd-truncate">{{ $voucher->pihak_terkait ?: '—' }}</td>
                                <td class="kd-num">{{ $isKeluar ? 'Pengeluaran' : 'Penerimaan' }}</td>
                                <td class="kd-amount kd-num">Rp {{ Format::nominal($voucher->total_nominal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <p class="kd-footnote">Ringkasan dihitung dari voucher dan saldo awal yang tercatat. Periode: {{ $start->translatedFormat('d M Y') }} – {{ $end->translatedFormat('d M Y') }}.</p>
</div>