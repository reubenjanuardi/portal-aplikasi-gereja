<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Realisasi Mingguan</title>
    <style>
        @page {
            size: A4 portrait;
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
            margin-bottom: 10px;
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
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 4px 0 2px 0;
            letter-spacing: 0.5px;
        }
        .period-subtitle {
            text-align: center;
            font-size: 8pt;
            color: #444;
            margin-bottom: 10px;
        }

        /* ── SUMMARY BOX ───────────────────────────────────── */
        .summary-box {
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            padding: 6px 10px;
            margin-bottom: 12px;
        }
        .summary-box table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .summary-box td {
            border: none;
            padding: 2.5px 4px;
            font-size: 8pt;
        }

        /* ── SECTION HEADER & TABLES ───────────────────────── */
        .section-title {
            font-size: 8.5pt;
            font-weight: bold;
            background-color: #f1f5f9;
            padding: 4px 8px;
            border: 1px solid #cbd5e1;
            border-bottom: none;
            margin-top: 10px;
            text-transform: uppercase;
            color: #0f172a;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 3px 5px;
            font-size: 7.5pt;
        }
        table.data-table th {
            background-color: #f8fafc;
            font-weight: bold;
            text-align: left;
            color: #334155;
            text-transform: uppercase;
            font-size: 7pt;
        }
        .group-row {
            background-color: #f8fafc;
            font-weight: bold;
            color: #0f172a;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace; }
        .tfoot-total td {
            background-color: #f1f5f9;
            font-weight: bold;
            border-top: 2px solid #94a3b8;
        }
        .page-break {
            page-break-before: always;
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

    <div class="doc-title">BUKU PEMBANTU REALISASI MINGGUAN</div>
    <div class="period-subtitle">
        @if(!empty($mingguKe))
            <strong>Minggu ke-{{ $mingguKe }}</strong> &nbsp;|&nbsp;
        @endif
        Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong>
    </div>

    {{-- RINGKASAN EKSEKUTIF --}}
    <div class="summary-box">
        <table>
            <tr>
                <td style="width: 25%;">Total Penerimaan:</td>
                <td style="width: 25%; font-weight: bold;" class="text-right font-mono">Rp {{ \App\Support\Format::nominal($reportData['totalPenerimaan'] ?? 0) }}</td>
                <td style="width: 25%;">Total Saldo Awal:</td>
                <td style="width: 25%; font-weight: bold;" class="text-right font-mono">Rp {{ \App\Support\Format::nominal($reportData['totalSaldoAwal'] ?? 0) }}</td>
            </tr>
            <tr>
                <td>Total Pengeluaran:</td>
                <td style="font-weight: bold;" class="text-right font-mono">Rp {{ \App\Support\Format::nominal($reportData['totalPengeluaran'] ?? 0) }}</td>
                <td>Total Saldo Akhir:</td>
                <td style="font-weight: bold;" class="text-right font-mono">Rp {{ \App\Support\Format::nominal($reportData['totalSaldoAkhir'] ?? 0) }}</td>
            </tr>
            <tr>
                <td>{{ ($reportData['surplusDefisit'] ?? 0) >= 0 ? 'Surplus Mingguan:' : 'Defisit Mingguan:' }}</td>
                <td style="font-weight: bold;" class="text-right font-mono">Rp {{ \App\Support\Format::nominal($reportData['surplusDefisit'] ?? 0) }}</td>
                <td colspan="2"></td>
            </tr>
        </table>
    </div>

    {{-- 1. PENERIMAAN --}}
    <div class="section-title">I. PENERIMAAN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;">No. MA</th>
                <th style="width: 57%;">Mata Anggaran</th>
                <th style="width: 25%;" class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['penerimaan'] ?? [] as $row)
                @php
                    $isGroup = $row['is_group'];
                    $depth = $row['depth'];
                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);
                @endphp
                <tr class="{{ $isGroup ? 'group-row' : '' }}">
                    <td class="font-mono">{{ $row['kode_akun'] }}</td>
                    <td>{!! $indent !!}{{ $row['nama_akun'] }}</td>
                    <td class="text-right font-mono">
                        {{ \App\Support\Format::nominal($row['amount']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="padding: 15px; color: #64748b;">Tidak ada mata anggaran penerimaan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="tfoot-total">
                <td colspan="2" class="text-right uppercase">TOTAL PENERIMAAN:</td>
                <td class="text-right font-mono">Rp {{ \App\Support\Format::nominal($reportData['totalPenerimaan'] ?? 0) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="page-break"></div>

    {{-- 2. PENGELUARAN --}}
    <div class="section-title">II. PENGELUARAN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;">No. MA</th>
                <th style="width: 57%;">Mata Anggaran</th>
                <th style="width: 25%;" class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['pengeluaran'] ?? [] as $row)
                @php
                    $isGroup = $row['is_group'];
                    $depth = $row['depth'];
                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);
                @endphp
                <tr class="{{ $isGroup ? 'group-row' : '' }}">
                    <td class="font-mono">{{ $row['kode_akun'] }}</td>
                    <td>{!! $indent !!}{{ $row['nama_akun'] }}</td>
                    <td class="text-right font-mono">
                        {{ \App\Support\Format::nominal($row['amount']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="padding: 15px; color: #64748b;">Tidak ada mata anggaran pengeluaran.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="tfoot-total">
                <td colspan="2" class="text-right uppercase">TOTAL PENGELUARAN:</td>
                <td class="text-right font-mono">Rp {{ \App\Support\Format::nominal($reportData['totalPengeluaran'] ?? 0) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- 3. SALDO KAS & BANK --}}
    <div class="section-title">III. POSISI SALDO KAS & BANK</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;">No. MA</th>
                <th style="width: 42%;">Mata Anggaran</th>
                <th style="width: 20%;" class="text-right">A. Saldo Awal (Rp)</th>
                <th style="width: 20%;" class="text-right">B. Saldo Akhir (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['kasBank'] ?? [] as $row)
                @php
                    $isGroup = $row['is_group'];
                    $depth = $row['depth'];
                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);
                @endphp
                <tr class="{{ $isGroup ? 'group-row' : '' }}">
                    <td class="font-mono">{{ $row['kode_akun'] }}</td>
                    <td>{!! $indent !!}{{ $row['nama_akun'] }}</td>
                    <td class="text-right font-mono">
                        {{ \App\Support\Format::nominal($row['saldo_awal']) }}
                    </td>
                    <td class="text-right font-mono">
                        {{ \App\Support\Format::nominal($row['saldo_akhir']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 15px; color: #64748b;">Tidak ada akun Kas & Bank.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="tfoot-total">
                <td colspan="2" class="text-right uppercase">TOTAL SALDO KAS & BANK:</td>
                <td class="text-right font-mono">Rp {{ \App\Support\Format::nominal($reportData['totalSaldoAwal'] ?? 0) }}</td>
                <td class="text-right font-mono">Rp {{ \App\Support\Format::nominal($reportData['totalSaldoAkhir'] ?? 0) }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
