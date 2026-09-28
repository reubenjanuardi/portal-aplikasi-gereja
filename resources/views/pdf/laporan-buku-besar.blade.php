<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Buku Besar</title>
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

        /* ── RINGKASAN KAS & BANK ─────────────────────────── */
        .kasbank-summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .kasbank-summary th,
        .kasbank-summary td {
            border: 1px solid #cbd5e1;
            padding: 4px 7px;
            font-size: 8pt;
        }
        .kasbank-summary th {
            background-color: #f1f5f9;
            text-transform: uppercase;
            font-size: 7.5pt;
        }
        .kasbank-summary td.num {
            text-align: right;
            font-family: monospace;
        }
        .kasbank-summary tr.total td {
            font-weight: bold;
            background-color: #e2e8f0;
        }

        /* ── ACCOUNT BOX & TABLES ──────────────────────────── */
        .account-box {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .account-header-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-bottom: none;
        }
        .account-header-table td {
            padding: 5px 8px;
            font-size: 9pt;
            font-weight: bold;
        }
        .kasbank-tag {
            display: inline-block;
            border: 1px solid #cbd5e1;
            background-color: #ede9fe;
            color: #5b21b6;
            padding: 0 4px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.data-table th {
            background-color: #f8fafc;
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
        .row-opening td {
            font-style: italic;
            color: #555;
            background-color: #f8fafc;
        }
        .total-row td {
            font-weight: bold;
            background-color: #f8fafc;
            border-top: 2px solid #cbd5e1;
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

    <div class="doc-title">LAPORAN BUKU BESAR</div>
    <div class="period-subtitle">
        Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong>
        @if(!empty($jenisVoucher))
            | Jenis: <strong>{{ $jenisVoucher }}</strong>
        @endif
        @if(!empty($kodeAkun))
            | Akun: <strong>{{ $kodeAkun }}</strong>
        @endif
    </div>

    {{-- Ringkasan saldo akun Kas & Bank --}}
    @php
        $kasBankAccounts = $reportData->where('is_kas_bank', true)->values();
        $totalSaldoKasBank = (float) $kasBankAccounts->sum('saldo_akhir');
    @endphp

    @if($kasBankAccounts->isNotEmpty())
        <table class="kasbank-summary">
            <thead>
                <tr>
                    <th style="width: 14%;">Kode Akun</th>
                    <th style="width: 36%;">Nama Akun Kas / Bank</th>
                    <th style="width: 17%;" class="text-right">Masuk (Debet)</th>
                    <th style="width: 17%;" class="text-right">Keluar (Kredit)</th>
                    <th style="width: 16%;" class="text-right">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kasBankAccounts as $kasBank)
                    <tr>
                        <td class="font-mono">{{ $kasBank['kode_akun'] }}</td>
                        <td>{{ $kasBank['nama_akun'] }}</td>
                        <td class="num">{{ number_format($kasBank['total_masuk'], 0, ',', '.') }}</td>
                        <td class="num">{{ number_format($kasBank['total_keluar'], 0, ',', '.') }}</td>
                        <td class="num">{{ number_format($kasBank['saldo_akhir'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td colspan="4" class="text-right">TOTAL SALDO KAS &amp; BANK</td>
                    <td class="num">{{ number_format($totalSaldoKasBank, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    @forelse($reportData as $account)
        @php
            $runningBalance = (float) $account['saldo_awal'];
        @endphp
        <div class="account-box">
            <table class="account-header-table">
                <tr>
                    <td style="width: 60%;">
                        Akun: <span class="font-mono">{{ $account['kode_akun'] }}</span> - {{ $account['nama_akun'] }}
                        <span style="font-weight: normal; font-size: 8pt; color: #555;">(Kategori: {{ $account['kategori'] ?? '-' }})</span>
                        @if($account['is_kas_bank'])
                            <span class="kasbank-tag">Kas &amp; Bank</span>
                        @endif
                    </td>
                    <td style="width: 40%; text-align: right; font-size: 8pt;">
                        Saldo Awal: Rp {{ number_format($account['saldo_awal'], 0, ',', '.') }} |
                        Masuk: Rp {{ number_format($account['total_masuk'], 0, ',', '.') }} |
                        Keluar: Rp {{ number_format($account['total_keluar'], 0, ',', '.') }} |
                        Saldo: Rp {{ number_format($account['saldo_akhir'], 0, ',', '.') }}
                    </td>
                </tr>
            </table>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">No. Bukti</th>
                        <th style="width: 9%;" class="text-center">Tanggal</th>
                        <th style="width: 18%;">Pihak Terkait</th>
                        <th style="width: 31%;">Uraian</th>
                        <th style="width: 10%;" class="text-right">Debet</th>
                        <th style="width: 10%;" class="text-right">Kredit</th>
                        <th style="width: 10%;" class="text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @if(round($account['saldo_awal'], 2) !== 0.0)
                        <tr class="row-opening">
                            <td colspan="6" class="text-right">
                                Saldo awal per {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}
                            </td>
                            <td class="text-right font-mono">{{ number_format($account['saldo_awal'], 0, ',', '.') }}</td>
                        </tr>
                    @endif

                    @foreach($account['lines'] as $line)
                        @php
                            $runningBalance += $line['debit'] - $line['kredit'];
                        @endphp
                        <tr>
                            <td class="font-mono" style="font-weight: bold;">{{ $line['no_bukti'] }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($line['tanggal'])->format('d/m/Y') }}</td>
                            <td>{{ $line['pihak_terkait'] ?? '-' }}</td>
                            <td>
                                {{ $line['uraian'] }}
                                <span style="font-size: 7pt; color: #666;">({{ $line['jenis_voucher'] }})</span>
                            </td>
                            <td class="text-right font-mono">{{ $line['debit'] > 0 ? number_format($line['debit'], 0, ',', '.') : '-' }}</td>
                            <td class="text-right font-mono">{{ $line['kredit'] > 0 ? number_format($line['kredit'], 0, ',', '.') : '-' }}</td>
                            <td class="text-right font-mono">{{ number_format($runningBalance, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4" class="text-right uppercase">Subtotal {{ $account['kode_akun'] }}:</td>
                        <td class="text-right font-mono">{{ number_format($account['total_masuk'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format($account['total_keluar'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format($account['saldo_akhir'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @empty
        <div style="text-align: center; color: #64748b; padding: 30px; border: 1px dashed #cbd5e1;">
            Tidak ada transaksi yang ditemukan untuk periode dan kriteria filter yang dipilih.
        </div>
    @endforelse

</body>
</html>
