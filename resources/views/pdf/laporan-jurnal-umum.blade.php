<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Jurnal Umum</title>
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
            padding: 6px 8px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
        }
        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 8px;
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
        .account-indent {
            padding-left: 24px !important;
        }
        .uraian-text {
            font-style: italic;
            color: #555;
            padding-left: 16px;
            font-size: 7.5pt;
        }

        /* ── SIGNATURES ────────────────────────────────────── */
        .signatures {
            margin-top: 28px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            vertical-align: top;
            font-size: 8.5pt;
        }
        .sig-space {
            height: 50px;
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

    <div class="doc-title">JURNAL UMUM (GENERAL JOURNAL)</div>
    <div class="period-subtitle">
        Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong>
        @if(!empty($jenisVoucher))
            | Jenis: <strong>{{ $jenisVoucher }}</strong>
        @endif
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 9%;" class="text-center">Tanggal</th>
                <th style="width: 12%;">No. Bukti</th>
                <th style="width: 7%;" class="text-center">Jenis</th>
                <th style="width: 15%;">Pihak Terkait</th>
                <th>Keterangan / Nama Rekening & Akun</th>
                <th style="width: 10%;" class="text-center">Ref (Kode)</th>
                <th style="width: 13%;" class="text-right">Debet (Rp)</th>
                <th style="width: 13%;" class="text-right">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalDebit = 0; 
                $totalKredit = 0; 
            @endphp
            @forelse($journalEntries as $entry)
                @php
                    $totalDebit += $entry['debit']['nominal'];
                    $totalKredit += $entry['kredit']['nominal'];
                    $isMasuk = in_array($entry['jenis_voucher'], ['Masuk', 'BKM', 'BBM'], true);
                @endphp
                {{-- Baris 1: Sisi Debet --}}
                <tr>
                    <td class="text-center font-medium" rowspan="3" style="vertical-align: top; border-bottom: 2px solid #94a3b8;">
                        {{ \Carbon\Carbon::parse($entry['tanggal'])->format('d/m/Y') }}
                    </td>
                    <td class="font-mono" style="font-weight: bold; vertical-align: top; border-bottom: 2px solid #94a3b8;" rowspan="3">
                        {{ $entry['no_bukti'] }}
                    </td>
                    <td class="text-center" style="vertical-align: top; border-bottom: 2px solid #94a3b8;" rowspan="3">
                        <span class="{{ $isMasuk ? 'badge-masuk' : 'badge-keluar' }}">
                            {{ $entry['jenis_voucher'] }}
                        </span>
                    </td>
                    <td style="vertical-align: top; border-bottom: 2px solid #94a3b8;" rowspan="3">
                        {{ $entry['pihak_terkait'] ?: '-' }}
                    </td>
                    <td style="font-weight: bold;">
                        {{ $entry['debit']['nama_akun'] }}
                    </td>
                    <td class="text-center font-mono font-bold">
                        {{ $entry['debit']['kode_akun'] }}
                    </td>
                    <td class="text-right font-mono font-bold">
                        {{ number_format($entry['debit']['nominal'], 0, ',', '.') }}
                    </td>
                    <td class="text-right font-mono text-gray-400">
                        -
                    </td>
                </tr>

                {{-- Baris 2: Sisi Kredit --}}
                <tr>
                    <td class="account-indent">
                        ↳ {{ $entry['kredit']['nama_akun'] }}
                    </td>
                    <td class="text-center font-mono font-bold">
                        {{ $entry['kredit']['kode_akun'] }}
                    </td>
                    <td class="text-right font-mono text-gray-400">
                        -
                    </td>
                    <td class="text-right font-mono font-bold">
                        {{ number_format($entry['kredit']['nominal'], 0, ',', '.') }}
                    </td>
                </tr>

                {{-- Baris 3: Uraian Transaksi --}}
                <tr style="border-bottom: 2px solid #94a3b8; background-color: #fafafa;">
                    <td colspan="4" class="uraian-text">
                        ({{ $entry['uraian'] }})
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 24px; color: #64748b;">
                        Tidak ada data jurnal umum untuk periode dan kriteria yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($journalEntries) > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" class="text-right uppercase">
                        TOTAL DEBET & KREDIT:
                    </td>
                    <td class="text-right font-mono" style="font-size: 9pt;">
                        Rp {{ number_format($totalDebit, 0, ',', '.') }}
                    </td>
                    <td class="text-right font-mono" style="font-size: 9pt;">
                        Rp {{ number_format($totalKredit, 0, ',', '.') }}
                    </td>
                </tr>
                <tr style="background-color: #f1f5f9;">
                    <td colspan="8" class="text-center" style="padding: 4px; font-weight: bold; font-size: 8pt; color: {{ round($totalDebit, 2) === round($totalKredit, 2) ? '#047857' : '#b91c1c' }};">
                        STATUS: {{ round($totalDebit, 2) === round($totalKredit, 2) ? '✓ SEIMBANG (BALANCE)' : '✕ TIDAK SEIMBANG' }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- SIGNATURE SECTION --}}
    <table class="signatures" style="page-break-inside: avoid;">
        <tr>
            <td style="width: 50%;">
                Mengetahui,<br>
                <strong>Ketua Majelis Jemaat</strong>
                <div class="sig-space"></div>
                ( .................................................... )
            </td>
            <td style="width: 50%;">
                Jakarta, {{ now()->translatedFormat('d F Y') }}<br>
                <strong>Bendahara / Kasir</strong>
                <div class="sig-space"></div>
                ( .................................................... )
            </td>
        </tr>
    </table>

</body>
</html>
