<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Jurnal Transaksi</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.2cm 1.5cm 1.2cm 1.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #111;
            line-height: 1.3;
        }

        /* ── HEADER KOP SURAT ──────────────────────────────── */
        .kop-header {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 12px;
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
            margin: 6px 0 4px 0;
            letter-spacing: 0.5px;
        }
        .period-subtitle {
            text-align: center;
            font-size: 8.5pt;
            color: #444;
            margin-bottom: 14px;
        }

        /* ── DATA TABLE ────────────────────────────────────── */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.data-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
        }
        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 7px;
            font-size: 8pt;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace; }
        .total-row td {
            font-weight: bold;
            background-color: #f8fafc;
            border-top: 2px solid #cbd5e1;
        }
        .badge-masuk {
            color: #047857;
            font-weight: bold;
        }
        .badge-keluar {
            color: #b91c1c;
            font-weight: bold;
        }
    </style>
</head>
<body>

    {{-- KOP HEADER --}}
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
            <td style="width: 30%; text-align: right; vertical-align: bottom; font-size: 8pt; color: #555;">
                Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </td>
        </tr>
    </table>

    <div class="doc-title">LAPORAN JURNAL TRANSAKSI</div>
    <div class="period-subtitle">
        Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong>
        @if(!empty($kategori))
            | Kategori Akun: <strong>{{ $kategori }}</strong>
        @endif
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">Tanggal</th>
                <th style="width: 12%;">No. Bukti</th>
                <th style="width: 8%;" class="text-center">Jenis</th>
                <th style="width: 18%;">Pihak Terkait</th>
                <th style="width: 22%;">Akun Anggaran</th>
                <th style="width: 20%;">Uraian</th>
                <th style="width: 12%;" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($reportData as $tx)
                @php
                    $grandTotal += $tx->nominal;
                    $isMasuk = in_array($tx->voucher->jenis_voucher ?? '', ['Masuk', 'BKM', 'BBM'], true);
                @endphp
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($tx->voucher->tanggal ?? now())->format('d/m/Y') }}</td>
                    <td class="font-mono" style="font-weight: bold;">{{ $tx->no_bukti }}</td>
                    <td class="text-center">
                        <span class="{{ $isMasuk ? 'badge-masuk' : 'badge-keluar' }}">
                            {{ $tx->voucher->jenis_voucher ?? '-' }}
                        </span>
                    </td>
                    <td>{{ $tx->voucher->pihak_terkait ?? '-' }}</td>
                    <td>
                        <span class="font-mono">{{ $tx->kode_akun }}</span> - {{ $tx->chartOfAccount->nama_akun ?? '-' }}
                    </td>
                    <td>{{ $tx->uraian }}</td>
                    <td class="text-right font-mono font-bold">
                        {{ \App\Support\Format::nominal($tx->nominal) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 24px; color: #64748b;">
                        Tidak ada transaksi yang ditemukan untuk periode dan filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($reportData) > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" class="text-right uppercase">GRAND TOTAL NOMINAL:</td>
                    <td class="text-right font-mono" style="font-size: 9pt;">
                        Rp {{ \App\Support\Format::nominal($grandTotal) }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>
</html>
