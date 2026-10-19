<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan Triwulan {{ $period['triwulan_label'] }} {{ $period['tahun_label'] }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.2cm 1.2cm 1.2cm 1.2cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #111;
            line-height: 1.25;
        }

        /* ── HEADER KOP SURAT ──────────────────────────────── */
        .kop-header {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .church-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .church-sub {
            font-size: 8pt;
            color: #444;
            margin-top: 2px;
        }
        .doc-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 6px 0 2px 0;
            letter-spacing: 0.5px;
        }
        .period-subtitle {
            text-align: center;
            font-size: 8pt;
            color: #444;
            margin-bottom: 8px;
        }

        /* ── SECTION HEADER & TABLES ───────────────────────── */
        .section-title {
            font-size: 8.5pt;
            font-weight: bold;
            background-color: #f1f5f9;
            padding: 4px 8px;
            border: 1px solid #cbd5e1;
            border-bottom: none;
            margin-top: 8px;
            text-transform: uppercase;
            color: #0f172a;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 2px 4px;
            font-size: 6.5pt;
        }
        table.data-table th {
            background-color: #f8fafc;
            font-weight: bold;
            text-align: left;
            color: #334155;
            text-transform: uppercase;
            font-size: 6.2pt;
        }
        .group-row {
            background-color: #f8fafc;
            font-weight: bold;
            color: #0f172a;
        }
        .section-row td {
            background-color: #e2e8f0;
            font-weight: bold;
            text-transform: uppercase;
        }
        .total-row td {
            background-color: #f1f5f9;
            font-weight: bold;
            border-top: 2px solid #94a3b8;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace; }
        .page-break { page-break-before: always; }
        .note-box {
            border: 1px solid #c7d2fe;
            background-color: #eef2ff;
            padding: 4px 8px;
            font-size: 6.5pt;
            color: #4338ca;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

    {{-- HEADER KOP --}}
    <table class="kop-header">
        <tr>
            <td style="width: 70%; vertical-align: top;">
                <div class="church-title">{{ $churchName ?? 'GPIB JEMAAT HOSIANA' }}</div>
                @if(!empty($churchAddress1))
                    <div class="church-sub">{{ $churchAddress1 }}</div>
                @endif
                @if(!empty($churchAddress2))
                    <div class="church-sub">{{ $churchAddress2 }}</div>
                @endif
            </td>
            <td style="width: 30%; text-align: right; vertical-align: bottom; font-size: 7.5pt; color: #555;">
                Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </td>
        </tr>
    </table>

    {{-- ============ HALAMAN 1: REKAPITULASI ============ --}}
    <div class="doc-title">REKAPITULASI LAPORAN REALISASI TRIWULAN</div>
    <div class="period-subtitle">
        <strong>{{ $period['triwulan_label'] }}</strong>
        &nbsp;|&nbsp;
        Periode: <strong>{{ \Carbon\Carbon::parse($period['start_date'])->translatedFormat('d F Y') }}</strong>
        s/d <strong>{{ \Carbon\Carbon::parse($period['end_date'])->translatedFormat('d F Y') }}</strong>
        &nbsp;|&nbsp; Tahun Anggaran <strong>{{ $period['tahun_label'] }}</strong>
        &nbsp;|&nbsp; Anggaran kumulatif {{ number_format($period['persen_anggaran'] * 100, 0) }}%
    </div>

    {{-- I. PENERIMAAN --}}
    <div class="section-title">I. Rekapitulasi Penerimaan</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 14%;">No. MA</th>
                <th style="width: 46%;">Uraian Anggaran</th>
                <th style="width: 20%;" class="text-right">Anggaran Setahun (Rp)</th>
                <th style="width: 20%;" class="text-right">Realisasi Triwulan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekap['penerimaan']['items'] as $row)
                @php
                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', (int) $row['depth']);
                @endphp
                <tr class="{{ $row['is_group'] ? 'group-row' : '' }}">
                    <td class="font-mono">{{ $row['kode_akun'] }}</td>
                    <td>{!! $indent !!}{{ $row['nama_akun'] }}</td>
                    <td class="text-right font-mono">{{ \App\Support\Format::nominal($row['anggaran_setahun']) }}</td>
                    <td class="text-right font-mono">{{ \App\Support\Format::nominal($row['realisasi']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 10px; color: #64748b;">Tidak ada mata anggaran penerimaan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTAL PENERIMAAN:</td>
                <td class="text-right font-mono">{{ \App\Support\Format::nominal($rekap['penerimaan']['total_anggaran']) }}</td>
                <td class="text-right font-mono">{{ \App\Support\Format::nominal($rekap['penerimaan']['total_realisasi']) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- II. PENGELUARAN --}}
    <div class="section-title">II. Rekapitulasi Pengeluaran</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">No. MA</th>
                <th style="width: 33%;">Uraian Pengeluaran</th>
                <th style="width: 16%;" class="text-right">Anggaran Setahun (Rp)</th>
                <th style="width: 14%;" class="text-right">Anggaran Triwulan (Rp)</th>
                <th style="width: 17%;" class="text-right">Realisasi Triwulan (Rp)</th>
                <th style="width: 8%;" class="text-center">%</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekap['pengeluaran']['items'] as $row)
                @php
                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', (int) $row['depth']);
                @endphp
                <tr class="{{ $row['is_group'] ? 'group-row' : '' }}">
                    <td class="font-mono">{{ $row['kode_akun'] }}</td>
                    <td>{!! $indent !!}{{ $row['nama_akun'] }}</td>
                    <td class="text-right font-mono">{{ \App\Support\Format::nominal($row['anggaran_setahun']) }}</td>
                    <td class="text-right font-mono">{{ \App\Support\Format::nominal($row['anggaran_triwulan']) }}</td>
                    <td class="text-right font-mono">{{ \App\Support\Format::nominal($row['realisasi']) }}</td>
                    <td class="text-center font-mono">
                        @if($row['persen'] !== null)
                            {{ number_format($row['persen'], 1) }}%
                        @else
                            &mdash;
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 10px; color: #64748b;">Tidak ada mata anggaran pengeluaran.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTAL PENGELUARAN:</td>
                <td class="text-right font-mono">{{ \App\Support\Format::nominal($rekap['pengeluaran']['total_anggaran']) }}</td>
                <td class="text-right font-mono">{{ \App\Support\Format::nominal($rekap['pengeluaran']['total_anggaran_triwulan']) }}</td>
                <td class="text-right font-mono">{{ \App\Support\Format::nominal($rekap['pengeluaran']['total_realisasi']) }}</td>
                <td class="text-center font-mono">
                    @if($rekap['pengeluaran']['total_persen'] !== null)
                        {{ number_format($rekap['pengeluaran']['total_persen'], 1) }}%
                    @else
                        &mdash;
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Ringkasan --}}
    <table class="data-table" style="width: 60%; margin-top: 4px;">
        <tbody>
            <tr>
                <td style="width: 60%;">Total Penerimaan Triwulan</td>
                <td class="text-right font-mono">{{ \App\Support\Format::nominal($totalPenerimaan) }}</td>
            </tr>
            <tr>
                <td>Total Pengeluaran Triwulan</td>
                <td class="text-right font-mono">{{ \App\Support\Format::nominal($totalPengeluaran) }}</td>
            </tr>
            <tr>
                <td><strong>{{ $surplusDefisit >= 0 ? 'Surplus' : 'Defisit' }} Triwulan</strong></td>
                <td class="text-right font-mono"><strong>{{ \App\Support\Format::nominal($surplusDefisit) }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- ============ HALAMAN 2+: DETAIL ============ --}}
    <div class="page-break"></div>

    <div class="doc-title">DETAIL LAPORAN REALISASI TRIWULAN</div>
    <div class="period-subtitle">
        <strong>{{ $period['triwulan_label'] }}</strong>
        &nbsp;|&nbsp;
        Periode: <strong>{{ \Carbon\Carbon::parse($period['start_date'])->translatedFormat('d F Y') }}</strong>
        s/d <strong>{{ \Carbon\Carbon::parse($period['end_date'])->translatedFormat('d F Y') }}</strong>
        &nbsp;|&nbsp; Tahun Anggaran <strong>{{ $period['tahun_label'] }}</strong>
    </div>

    <div class="note-box">
        Catatan: kolom <strong>Real to Plan</strong> = realisasi kumulatif sepanjang triwulan &divide; anggaran kumulatif triwulan (25/50/75/100% anggaran tahunan).
        Baris induk menjumlahkan nilai anak tanpa hitung ganda.
    </div>

    <table class="data-table">
        <thead>
            @php
                $monthKeys = array_column($period['months'], 'key');
                $monthLabels = array_column($period['months'], 'label');
                // No. MA + Mata Anggaran + Anggaran Tahunan (2) + per bulan (2) + Real to Plan (2)
                $spanCols = 4 + (count($monthKeys) * 2) + 2;
            @endphp
            <tr>
                <th rowspan="2" style="width: 8%;">No. MA</th>
                <th rowspan="2" style="width: 18%;">Mata Anggaran</th>
                <th colspan="2" class="text-center">Anggaran Tahunan (Rp)</th>
                @foreach($monthLabels as $label)
                    <th colspan="2" class="text-center">{{ $label }} (Rp)</th>
                @endforeach
                <th colspan="2" class="text-center">Real to Plan Triwulan (%)</th>
            </tr>
            <tr>
                <th class="text-center">Masuk</th>
                <th class="text-center">Keluar</th>
                @foreach($monthKeys as $label)
                    <th class="text-center">Masuk</th>
                    <th class="text-center">Keluar</th>
                @endforeach
                <th class="text-center">Masuk</th>
                <th class="text-center">Keluar</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-row">
                <td colspan="{{ $spanCols }}">PENERIMAAN</td>
            </tr>
            @forelse($rekap['penerimaan']['items'] as $row)
                @php
                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', (int) $row['depth']);
                @endphp
                <tr class="{{ $row['is_group'] ? 'group-row' : '' }}">
                    <td class="font-mono">{{ $row['kode_akun'] }}</td>
                    <td>{!! $indent !!}{{ $row['nama_akun'] }}</td>
                    <td class="text-right font-mono">{{ \App\Support\Format::nominal($row['anggaran_setahun']) }}</td>
                    <td class="text-center">&mdash;</td>
                    @foreach($monthKeys as $key)
                        <td class="text-right font-mono">{{ \App\Support\Format::nominal($row['monthly'][$key] ?? 0) }}</td>
                        <td class="text-center">&mdash;</td>
                    @endforeach
                    {{-- Real to Plan kumulatif triwulan --}}
                    <td class="text-center font-mono">
                        @if($row['persen'] !== null)
                            {{ number_format($row['persen'], 1) }}%
                        @else
                            &mdash;
                        @endif
                    </td>
                    <td class="text-center">&mdash;</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $spanCols }}" class="text-center" style="padding: 10px; color: #64748b;">Tidak ada mata anggaran penerimaan.</td>
                </tr>
            @endforelse

            <tr class="section-row">
                <td colspan="{{ $spanCols }}">PENGELUARAN</td>
            </tr>
            @forelse($rekap['pengeluaran']['items'] as $row)
                @php
                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', (int) $row['depth']);
                @endphp
                <tr class="{{ $row['is_group'] ? 'group-row' : '' }}">
                    <td class="font-mono">{{ $row['kode_akun'] }}</td>
                    <td>{!! $indent !!}{{ $row['nama_akun'] }}</td>
                    <td class="text-center">&mdash;</td>
                    <td class="text-right font-mono">{{ \App\Support\Format::nominal($row['anggaran_setahun']) }}</td>
                    @foreach($monthKeys as $key)
                        <td class="text-center">&mdash;</td>
                        <td class="text-right font-mono">{{ \App\Support\Format::nominal($row['monthly'][$key] ?? 0) }}</td>
                    @endforeach
                    {{-- Real to Plan kumulatif triwulan --}}
                    <td class="text-center">&mdash;</td>
                    <td class="text-center font-mono">
                        @if($row['persen'] !== null)
                            {{ number_format($row['persen'], 1) }}%
                        @else
                            &mdash;
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $spanCols }}" class="text-center" style="padding: 10px; color: #64748b;">Tidak ada mata anggaran pengeluaran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>