<x-filament-panels::page>
    <style>
        /* ── Palette (slate/indigo, explicit light+dark) ────────────── */
        .lt-page {
            --lt-surface: #ffffff;
            --lt-muted-surface: #f8fafc;
            --lt-border: #e5e7eb;
            --lt-border-strong: #cbd5e1;
            --lt-body: #334155;
            --lt-heading: #0f172a;
            --lt-muted: #475569;
            --lt-th-bg: #f1f5f9;
            --lt-th-text: #475569;
            --lt-th-border: #cbd5e1;
            --lt-td-border: #f1f5f9;
            --lt-hover: #f8fafc;
            --lt-in: #059669;
            --lt-in-bg: #ecfdf5;
            --lt-in-border: #a7f3d0;
            --lt-out: #e11d48;
            --lt-out-bg: #fff1f2;
            --lt-out-border: #fecdd3;
            --lt-accent: #4f46e5;
            --lt-accent-bg: #eef2ff;
            --lt-accent-border: #c7d2fe;
            --lt-def: #d97706;
            --lt-def-bg: #fffbeb;
            --lt-def-border: #fde68a;
            --lt-row-depth0: #f1f5f9;
            --lt-row-depth0-text: #0f172a;
            --lt-row-depth1: #f8fafc;
            --lt-row-depth1-text: #1e293b;
        }
        :where(.dark, .dark *) .lt-page {
            --lt-surface: #18181b;
            --lt-muted-surface: rgba(255,255,255,0.02);
            --lt-border: rgba(255,255,255,0.1);
            --lt-border-strong: rgba(255,255,255,0.08);
            --lt-body: #d1d5db;
            --lt-heading: #f9fafb;
            --lt-muted: #9ca3af;
            --lt-th-bg: rgba(255,255,255,0.02);
            --lt-th-text: #9ca3af;
            --lt-th-border: rgba(255,255,255,0.08);
            --lt-td-border: rgba(255,255,255,0.04);
            --lt-hover: rgba(255,255,255,0.03);
            --lt-in: #34d399;
            --lt-in-bg: rgba(16,185,129,0.15);
            --lt-in-border: rgba(52,211,153,0.3);
            --lt-out: #fb7185;
            --lt-out-bg: rgba(244,63,94,0.15);
            --lt-out-border: rgba(251,113,133,0.3);
            --lt-accent: #818cf8;
            --lt-accent-bg: rgba(99,102,241,0.15);
            --lt-accent-border: rgba(129,140,248,0.3);
            --lt-def: #fbbf24;
            --lt-def-bg: rgba(245,158,11,0.15);
            --lt-def-border: rgba(251,191,36,0.3);
            --lt-row-depth0: rgba(255,255,255,0.05);
            --lt-row-depth0-text: #f9fafb;
            --lt-row-depth1: rgba(255,255,255,0.02);
            --lt-row-depth1-text: #e5e7eb;
        }

        /* ── Layout helpers ──────────────────────────────────────────── */
        .lt-card {
            background: var(--lt-surface);
            border: 1px solid var(--lt-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .lt-section-header {
            padding: 12px 16px;
            border-bottom: 1px solid var(--lt-border-strong);
            background: var(--lt-muted-surface);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .lt-section-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--lt-heading);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .lt-section-badge {
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid;
        }
        .lt-badge-in {
            background: var(--lt-in-bg);
            color: var(--lt-in);
            border-color: var(--lt-in-border);
        }
        .lt-badge-out {
            background: var(--lt-out-bg);
            color: var(--lt-out);
            border-color: var(--lt-out-border);
        }
        .lt-badge-acc {
            background: var(--lt-accent-bg);
            color: var(--lt-accent);
            border-color: var(--lt-accent-border);
        }
        .lt-badge-def {
            background: var(--lt-def-bg);
            color: var(--lt-def);
            border-color: var(--lt-def-border);
        }

        /* ── Table ───────────────────────────────────────────────────── */
        .lt-table-wrap {
            width: 100%;
            overflow-x: auto;
        }
        .lt-table {
            width: 100%;
            border-collapse: collapse !important;
            font-size: 12.5px;
        }
        .lt-table th {
            background: var(--lt-th-bg);
            color: var(--lt-th-text);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: 0.5px;
            padding: 9px 12px;
            border-bottom: 1px solid var(--lt-th-border);
            text-align: left;
            white-space: nowrap;
        }
        .lt-table th.num {
            text-align: right;
        }
        .lt-table th.center {
            text-align: center;
        }
        .lt-table td {
            padding: 7px 12px;
            border-bottom: 1px solid var(--lt-td-border);
            color: var(--lt-body);
            vertical-align: middle;
        }
        .lt-table tr:hover td {
            background: var(--lt-hover);
        }
        .lt-table .lt-depth-0 td {
            background: var(--lt-row-depth0) !important;
            font-weight: 700;
            color: var(--lt-row-depth0-text) !important;
            border-top: 1px solid var(--lt-border-strong);
            border-bottom: 1px solid var(--lt-border-strong);
        }
        .lt-table .lt-depth-1 td {
            background: var(--lt-row-depth1) !important;
            font-weight: 600;
            color: var(--lt-row-depth1-text) !important;
        }
        .lt-table .lt-section-row td {
            background: var(--lt-muted-surface) !important;
            font-weight: 700;
            color: var(--lt-heading) !important;
            border-top: 1px solid var(--lt-border-strong);
        }
        .lt-table .lt-total-row td {
            background: var(--lt-row-depth0) !important;
            font-weight: 800;
            color: var(--lt-row-depth0-text) !important;
            border-top: 2px solid var(--lt-border-strong);
        }
        .lt-col-code {
            font-family: ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;
            font-size: 11.5px;
            color: var(--lt-muted);
            white-space: nowrap;
        }
        .lt-col-num {
            font-family: ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .lt-col-num.bold {
            font-weight: 700;
            color: var(--lt-heading);
        }
        .lt-col-num.muted {
            color: var(--lt-muted);
        }
        .lt-col-num.pct {
            font-weight: 600;
        }
        .lt-col-num.in { color: var(--lt-in); }
        .lt-col-num.out { color: var(--lt-out); }
        .lt-col-num.pct.pos { color: var(--lt-in); }
        .lt-col-num.pct.neg { color: var(--lt-out); }
        .lt-indent { display: inline-block; }
        .lt-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
            background: var(--lt-muted);
        }
        .lt-empty {
            text-align: center;
            padding: 24px !important;
            color: var(--lt-muted);
        }

        /* ── Period header ──────────────────────────────────────────── */
        .lt-period-card {
            background: var(--lt-surface);
            border: 1px solid var(--lt-border);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
        }
        .lt-period-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--lt-heading);
        }
        .lt-period-sub {
            font-size: 12px;
            color: var(--lt-muted);
        }
        .lt-kpi-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 8px;
        }
        .lt-kpi {
            background: var(--lt-muted-surface);
            border: 1px solid var(--lt-border);
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            min-width: 180px;
        }
        .lt-kpi-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--lt-muted);
            letter-spacing: 0.4px;
        }
        .lt-kpi-value {
            font-size: 16px;
            font-weight: 700;
            font-family: ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;
            color: var(--lt-heading);
            margin-top: 4px;
        }
        .lt-kpi-value.in { color: var(--lt-in); }
        .lt-kpi-value.out { color: var(--lt-out); }
        .lt-kpi-value.def { color: var(--lt-def); }
        .lt-kpi-value.acc { color: var(--lt-accent); }

        /* ── Note box ───────────────────────────────────────────────── */
        .lt-note {
            background: var(--lt-accent-bg);
            border: 1px solid var(--lt-accent-border);
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 12px;
            color: var(--lt-accent);
        }
        .lt-note strong { color: var(--lt-accent); }

        /* ── Print tweaks ───────────────────────────────────────────── */
        @media print {
            .lt-table-wrap { overflow-x: visible; }
            .no-print { display: none !important; }
        }
    </style>

    @php
        $data = $this->reportData;
        $period = $data['period'] ?? null;
        $rekap = $data['rekap'] ?? null;
        $detail = $data['detail'] ?? [];
        $totalPenerimaan = $data['totalPenerimaan'] ?? 0;
        $totalPengeluaran = $data['totalPengeluaran'] ?? 0;
        $surplusDefisit = $data['surplusDefisit'] ?? 0;
        $hasData = $totalPenerimaan > 0 || $totalPengeluaran > 0;
    @endphp

    {{-- Form Filter Periode --}}
    <x-filament::section class="no-print">
        {{ $this->form }}
    </x-filament::section>

    @if(!$period)
        <div class="lt-card lt-note">
            <strong>Pilih tahun anggaran dan triwulan</strong> untuk menampilkan laporan.
        </div>
    @else
        {{-- Periode & KPI Ringkasan --}}
        <div class="lt-page lt-period-card">
            <div>
                <div class="lt-period-title">{{ $period['triwulan_label'] }} Tahun Anggaran {{ $period['tahun_label'] }}</div>
                <div class="lt-period-sub">
                    Periode: {{ \Carbon\Carbon::parse($period['start_date'])->translatedFormat('d F Y') }} s.d.
                    {{ \Carbon\Carbon::parse($period['end_date'])->translatedFormat('d F Y') }}
                    &nbsp;|&nbsp; Anggaran kumulatif: {{ number_format($period['persen_anggaran'] * 100, 0) }}% dari anggaran tahunan
                </div>
            </div>
            <div class="lt-kpi-row">
                <div class="lt-kpi">
                    <span class="lt-kpi-label">Total Penerimaan</span>
                    <span class="lt-kpi-value in">Rp {{ \App\Support\Format::nominal($totalPenerimaan) }}</span>
                </div>
                <div class="lt-kpi">
                    <span class="lt-kpi-label">Total Pengeluaran</span>
                    <span class="lt-kpi-value out">Rp {{ \App\Support\Format::nominal($totalPengeluaran) }}</span>
                </div>
                <div class="lt-kpi">
                    <span class="lt-kpi-label">{{ $surplusDefisit >= 0 ? 'Surplus' : 'Defisit' }} Triwulan</span>
                    <span class="lt-kpi-value {{ $surplusDefisit >= 0 ? 'in' : 'def' }}">
                        Rp {{ \App\Support\Format::nominal($surplusDefisit) }}
                    </span>
                </div>
            </div>
        </div>

        @if(!$hasData)
            <div class="lt-note">
                <strong>Tidak ada data transaksi</strong> pada periode ini.
                @if($period['triwulan'] === 1 && $period['tahun'] === 2026)
                    (Data sistem mulai tercatat sejak Triwulan II TA 2026–2027.)
                @endif
            </div>
        @endif

        {{-- REKAPITULASI — Halaman Pertama --}}
        {{-- 1. PENERIMAAN --}}
        <div class="lt-page lt-card" style="margin-bottom: 24px;">
            <div class="lt-section-header">
                <span class="lt-section-title">I. Rekapitulasi Penerimaan</span>
                <span class="lt-section-badge lt-badge-in">
                    Total: Rp {{ \App\Support\Format::nominal($rekap['penerimaan']['total_realisasi'] ?? 0) }}
                </span>
            </div>
            <div class="lt-table-wrap">
                <table class="lt-table">
                    <thead>
                        <tr>
                            <th style="width: 150px;">No. MA</th>
                            <th>Uraian Anggaran</th>
                            <th class="num" style="width: 220px;">Anggaran Setahun (Rp)</th>
                            <th class="num" style="width: 220px;">Realisasi Triwulan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekap['penerimaan']['items'] as $row)
                            @php
                                $depth = $row['depth'];
                                $indent = $depth * 16;
                            @endphp
                            <tr class="lt-depth-{{ $depth }}">
                                <td class="lt-col-code">{{ $row['kode_akun'] }}</td>
                                <td style="padding-left: {{ 12 + $indent }}px;">
                                    @if(!$row['is_postable'] || !$row['is_group'])
                                        <span class="lt-dot"></span>
                                    @endif
                                    {{ $row['nama_akun'] }}
                                </td>
                                <td class="lt-col-num {{ $row['anggaran_setahun'] > 0 ? 'bold' : 'muted' }}">
                                    {{ \App\Support\Format::nominal($row['anggaran_setahun']) }}
                                </td>
                                <td class="lt-col-num {{ $row['realisasi'] > 0 ? 'bold' : 'muted' }}">
                                    {{ \App\Support\Format::nominal(abs($row['realisasi'])) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="lt-empty">Tidak ada mata anggaran penerimaan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="lt-total-row">
                            <td colspan="2" class="text-right uppercase tracking-wider text-xs font-bold">
                                TOTAL PENERIMAAN:
                            </td>
                            <td class="lt-col-num bold">{{ \App\Support\Format::nominal($rekap['penerimaan']['total_anggaran'] ?? 0) }}</td>
                            <td class="lt-col-num bold in">{{ \App\Support\Format::nominal($rekap['penerimaan']['total_realisasi'] ?? 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- 2. PENGELUARAN --}}
        <div class="lt-page lt-card" style="margin-bottom: 24px;">
            <div class="lt-section-header">
                <span class="lt-section-title">II. Rekapitulasi Pengeluaran</span>
                <span class="lt-section-badge lt-badge-out">
                    Total: Rp {{ \App\Support\Format::nominal($rekap['pengeluaran']['total_realisasi'] ?? 0) }}
                </span>
            </div>
            <div class="lt-table-wrap">
                <table class="lt-table">
                    <thead>
                        <tr>
                            <th style="width: 150px;">No. MA</th>
                            <th>Uraian Pengeluaran</th>
                            <th class="num" style="width: 200px;">Anggaran Setahun (Rp)</th>
                            <th class="num" style="width: 200px;">Anggaran Triwulan (Rp)</th>
                            <th class="num" style="width: 220px;">Realisasi Triwulan (Rp)</th>
                            <th class="center" style="width: 100px;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekap['pengeluaran']['items'] as $row)
                            @php
                                $depth = $row['depth'];
                                $indent = $depth * 16;
                            @endphp
                            <tr class="lt-depth-{{ $depth }}">
                                <td class="lt-col-code">{{ $row['kode_akun'] }}</td>
                                <td style="padding-left: {{ 12 + $indent }}px;">
                                    @if(!$row['is_postable'] || !$row['is_group'])
                                        <span class="lt-dot"></span>
                                    @endif
                                    {{ $row['nama_akun'] }}
                                </td>
                                <td class="lt-col-num {{ $row['anggaran_setahun'] > 0 ? 'bold' : 'muted' }}">
                                    {{ \App\Support\Format::nominal($row['anggaran_setahun']) }}
                                </td>
                                <td class="lt-col-num {{ $row['anggaran_triwulan'] > 0 ? 'bold' : 'muted' }}">
                                    {{ \App\Support\Format::nominal($row['anggaran_triwulan']) }}
                                </td>
                                <td class="lt-col-num {{ $row['realisasi'] > 0 ? 'bold' : 'muted' }}">
                                    {{ \App\Support\Format::nominal(abs($row['realisasi'])) }}
                                </td>
                                <td class="center lt-col-num pct {{ $row['persen'] !== null && $row['persen'] >= 100 ? 'pos' : '' }}">
                                    @if($row['persen'] !== null)
                                        {{ number_format($row['persen'], 1) }}%
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="lt-empty">Tidak ada mata anggaran pengeluaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="lt-total-row">
                            <td colspan="2" class="text-right uppercase tracking-wider text-xs font-bold">
                                TOTAL PENGELUARAN:
                            </td>
                            <td class="lt-col-num bold">{{ \App\Support\Format::nominal($rekap['pengeluaran']['total_anggaran'] ?? 0) }}</td>
                            <td class="lt-col-num bold">{{ \App\Support\Format::nominal($rekap['pengeluaran']['total_anggaran_triwulan'] ?? 0) }}</td>
                            <td class="lt-col-num bold out">{{ \App\Support\Format::nominal($rekap['pengeluaran']['total_realisasi'] ?? 0) }}</td>
                            <td class="center lt-col-num pct {{ $rekap['pengeluaran']['total_persen'] !== null && $rekap['pengeluaran']['total_persen'] >= 100 ? 'pos' : '' }}">
                                @if($rekap['pengeluaran']['total_persen'] !== null)
                                    {{ number_format($rekap['pengeluaran']['total_persen'], 1) }}%
                                @else
                                    &mdash;
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- DETAIL LAPORAN — Halaman Berikutnya --}}
        <div class="lt-page lt-card">
            <div class="lt-section-header">
                <span class="lt-section-title">III. Detail Laporan Triwulan</span>
                <span class="lt-section-badge lt-badge-acc">
                    {{ count($detail) }} baris
                </span>
            </div>

            <p class="lt-note" style="margin: 12px 0; font-size: 11px;">
                <strong>Catatan:</strong>
                <br>• Kolom <strong>Real to Plan</strong> = realisasi kumulatif sepanjang triwulan &divide; anggaran kumulatif triwulan (25/50/75/100% anggaran tahunan).
                <br>• Baris induk menjumlahkan nilai anak; tidak terjadi hitung ganda.
            </p>

            <div class="lt-table-wrap">
                @php
                    $monthKeys = array_column($period['months'], 'key');
                    $monthLabels = array_column($period['months'], 'label');
                    // No. MA + Mata Anggaran + Anggaran Tahunan (2) + per bulan (2) + Real to Plan (2)
                    $detailColCount = 4 + (count($monthLabels) * 2) + 2;
                @endphp
                <table class="lt-table" style="font-size: 11px;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 130px; vertical-align: middle;">No. MA</th>
                            <th rowspan="2" style="width: 260px; vertical-align: middle;">Mata Anggaran</th>
                            <th colspan="2" class="center">Anggaran Tahunan (Rp)</th>
                            @foreach($monthLabels as $label)
                                <th colspan="2" class="center">{{ $label }} (Rp)</th>
                            @endforeach
                            <th colspan="2" class="center">Real to Plan Triwulan (%)</th>
                        </tr>
                        <tr>
                            <th class="center">Masuk</th>
                            <th class="center">Keluar</th>
                            @foreach($monthLabels as $label)
                                <th class="center">Masuk</th>
                                <th class="center">Keluar</th>
                            @endforeach
                            <th class="center">Masuk</th>
                            <th class="center">Keluar</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Section Penerimaan --}}
                        <tr class="lt-section-row">
                            <td colspan="{{ $detailColCount }}">PENERIMAAN</td>
                        </tr>
                        @foreach($rekap['penerimaan']['items'] as $row)
                            @php
                                $depth = $row['depth'];
                                $indent = $depth * 16;
                            @endphp
                            <tr class="lt-depth-{{ $depth }}">
                                <td class="lt-col-code">{{ $row['kode_akun'] }}</td>
                                <td style="padding-left: {{ 12 + $indent }}px;">
                                    @if(!$row['is_postable'] || !$row['is_group'])
                                        <span class="lt-dot"></span>
                                    @endif
                                    {{ $row['nama_akun'] }}
                                </td>
                                {{-- Anggaran Tahunan --}}
                                <td class="lt-col-num {{ $row['anggaran_setahun'] > 0 ? 'bold' : 'muted' }}">
                                    {{ \App\Support\Format::nominal($row['anggaran_setahun']) }}
                                </td>
                                <td class="lt-col-num muted">&mdash;</td>
                                {{-- Bulan: Masuk/Keluar --}}
                                @foreach($monthKeys as $key)
                                    <td class="lt-col-num {{ ($row['monthly'][$key] ?? 0) > 0 ? 'bold' : 'muted' }}">
                                        {{ \App\Support\Format::nominal($row['monthly'][$key] ?? 0) }}
                                    </td>
                                    <td class="lt-col-num muted">&mdash;</td>
                                @endforeach
                                {{-- Real to Plan kumulatif triwulan --}}
                                <td class="lt-col-num pct {{ $row['persen'] !== null && $row['persen'] >= 100 ? 'pos' : '' }}">
                                    @if($row['persen'] !== null)
                                        {{ number_format($row['persen'], 1) }}%
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td class="lt-col-num muted">&mdash;</td>
                            </tr>
                        @endforeach
                        @if(empty($rekap['penerimaan']['items']))
                            <tr>
                                <td colspan="{{ $detailColCount }}" class="lt-empty">
                                    Tidak ada mata anggaran penerimaan.
                                </td>
                            </tr>
                        @endif

                        {{-- Section Pengeluaran --}}
                        <tr class="lt-section-row">
                            <td colspan="{{ $detailColCount }}">PENGELUARAN</td>
                        </tr>
                        @foreach($rekap['pengeluaran']['items'] as $row)
                            @php
                                $depth = $row['depth'];
                                $indent = $depth * 16;
                            @endphp
                            <tr class="lt-depth-{{ $depth }}">
                                <td class="lt-col-code">{{ $row['kode_akun'] }}</td>
                                <td style="padding-left: {{ 12 + $indent }}px;">
                                    @if(!$row['is_postable'] || !$row['is_group'])
                                        <span class="lt-dot"></span>
                                    @endif
                                    {{ $row['nama_akun'] }}
                                </td>
                                {{-- Anggaran Tahunan --}}
                                <td class="lt-col-num muted">&mdash;</td>
                                <td class="lt-col-num {{ $row['anggaran_setahun'] > 0 ? 'bold' : 'muted' }}">
                                    {{ \App\Support\Format::nominal($row['anggaran_setahun']) }}
                                </td>
                                {{-- Bulan: Masuk/Keluar --}}
                                @foreach($monthKeys as $key)
                                    <td class="lt-col-num muted">&mdash;</td>
                                    <td class="lt-col-num {{ ($row['monthly'][$key] ?? 0) > 0 ? 'bold' : 'muted' }}">
                                        {{ \App\Support\Format::nominal($row['monthly'][$key] ?? 0) }}
                                    </td>
                                @endforeach
                                {{-- Real to Plan kumulatif triwulan --}}
                                <td class="lt-col-num muted">&mdash;</td>
                                <td class="lt-col-num pct {{ $row['persen'] !== null && $row['persen'] >= 100 ? 'pos' : '' }}">
                                    @if($row['persen'] !== null)
                                        {{ number_format($row['persen'], 1) }}%
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if(empty($rekap['pengeluaran']['items']))
                            <tr>
                                <td colspan="{{ $detailColCount }}" class="lt-empty">
                                    Tidak ada mata anggaran pengeluaran.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>