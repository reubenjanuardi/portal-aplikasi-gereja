<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Voucher - {{ $voucher->no_bukti }}</title>
    <style>
        @page {
            size: a5 portrait;
            margin: 0.8cm 0.8cm 0.8cm 0.8cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #000;
            line-height: 1.3;
        }

        /* ── TOP HEADER ─────────────────────────────────────── */
        .top-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .top-header td {
            vertical-align: top;
            padding: 0;
        }
        .church-info {
            font-size: 8pt;
            line-height: 1.4;
        }
        .church-info .church-name {
            font-size: 9.5pt;
            font-weight: bold;
        }
        .no-box {
            width: 215px;
            text-align: right;
        }
        .no-box .no-label {
            font-size: 8.5pt;
            margin-bottom: 2px;
        }
        .account-box {
            width: 215px;
            border-collapse: collapse;
            border: 1px solid #000;
        }
        .account-box th {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
        }
        .account-box td {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 7.5pt;
            vertical-align: middle;
        }

        /* ── DOCUMENT TITLE ─────────────────────────────────── */
        .doc-title {
            text-align: center;
            font-size: 10.5pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 8px 0 10px 0;
            letter-spacing: 0.5px;
        }

        /* ── META INFO (Terima dari, Terbilang) ─────────────── */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5pt;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .meta-label {
            width: 85px;
            font-weight: bold;
            white-space: nowrap;
        }
        .meta-colon {
            width: 10px;
        }

        /* ── ITEMS TABLE ────────────────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .items-table th {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 8.5pt;
            font-weight: bold;
            text-align: center;
            background-color: #fff;
        }
        .items-table td {
            border: 1px solid #000;
            padding: 3px 6px;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .items-table .no-col  { width: 8%;  text-align: center; }
        .items-table .desc-col { width: 62%; }
        .items-table .amt-col  { width: 30%; text-align: right; }
        .items-table .item-row td {
            height: 20px;
        }
        /* Blank filler rows to give the table a fixed visual height */
        .items-table .blank-row td {
            height: 20px;
        }
        .total-row td {
            font-weight: bold;
            font-size: 8.5pt;
            background-color: #fff;
        }
        .total-label {
            text-align: right;
            border: 1px solid #000;
        }
        .total-amount {
            text-align: right;
            border: 1px solid #000;
        }

        /* ── SIGNATURE SECTION ──────────────────────────────── */
        .signature-wrapper {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .signature-wrapper > tbody > tr > td {
            vertical-align: top;
            padding: 0;
        }

        /* Left: 3-box signature block */
        .sig-left-table {
            border-collapse: collapse;
            width: 220px;
        }
        .sig-left-table th {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 8pt;
            font-weight: bold;
            text-align: center;
            width: 73px;
        }
        .sig-left-table td {
            border: 1px solid #000;
            height: 50px;
            width: 73px;
            vertical-align: bottom;
            padding: 3px 4px;
            font-size: 7.5pt;
        }

        /* Right: city + penerima */
        .sig-right {
            text-align: right;
            vertical-align: top;
            padding-top: 0;
            font-size: 8pt;
        }
        .sig-right .city-line {
            margin-bottom: 40px;
        }
        .sig-right .penerima-label {
            font-weight: bold;
        }
    </style>
</head>
<body>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- TOP HEADER: Church info (left) + No + Account (right) --}}
    {{-- ══════════════════════════════════════════════════ --}}
    <table class="top-header">
        <tr>
            {{-- Left: church identity --}}
            <td class="church-info">
                <div class="church-name">{{ $settings['church_name'] }}</div>
                <div>{{ $settings['church_address1'] }}</div>
                <div>{{ $settings['church_address2'] }}</div>
            </td>

            {{-- Right: No + account code box --}}
            <td class="no-box">
                <div class="no-label"><strong>No :</strong> {{ $voucher->no_bukti }}</div>
                <table class="account-box">
                    <thead>
                        <tr>
                            <th style="width:28%;">Akun</th>
                            <th style="width:32%;">Kode</th>
                            <th style="width:40%;">Nama Account</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: center; font-weight: bold;">{{ $voucher->isBank() ? 'Bank' : 'Kas' }}</td>
                            <td style="text-align: center; font-family: monospace;">{{ $voucher->kode_akun_kas_bank ?? '-' }}</td>
                            <td style="text-align: left;">{{ $voucher->akunKasBank->nama_akun ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-weight: bold;">Anggaran</td>
                            <td style="text-align: center; font-family: monospace;">{{ $voucher->kode_akun ?? '-' }}</td>
                            <td style="text-align: left;">{{ $voucher->chartOfAccount->nama_akun ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- DOCUMENT TITLE --}}
    {{-- ══════════════════════════════════════════════════ --}}
    @php
        $voucherTitle = match($voucher->jenis_voucher) {
            'BKM' => 'BUKTI KAS MASUK (BKM)',
            'BKK' => 'BUKTI KAS KELUAR (BKK)',
            'BBM' => 'BUKTI BANK MASUK (BBM)',
            'BBK' => 'BUKTI BANK KELUAR (BBK)',
            'Masuk' => 'BUKTI KAS / BANK MASUK',
            'Keluar' => 'BUKTI KAS / BANK KELUAR',
            default => 'BUKTI KAS / BANK ' . strtoupper($voucher->jenis_voucher),
        };
        $pihakLabel = (method_exists($voucher, 'isMasuk') ? $voucher->isMasuk() : ($voucher->jenis_voucher === 'Masuk'))
            ? 'Terima dari'
            : 'Dibayarkan kepada';
    @endphp
    <div class="doc-title">
        {{ $voucherTitle }}
    </div>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- TERIMA DARI / DIBAYARKAN KEPADA + TERBILANG --}}
    {{-- ══════════════════════════════════════════════════ --}}
    <table class="meta-table">
        <tr>
            <td class="meta-label">{{ $pihakLabel }}</td>
            <td class="meta-colon">:</td>
            <td>{{ $voucher->pihak_terkait }}</td>
        </tr>
        <tr>
            <td class="meta-label">Terbilang</td>
            <td class="meta-colon">:</td>
            <td><em>{{ $terbilang }}</em></td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- ITEM TABLE --}}
    {{-- ══════════════════════════════════════════════════ --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="no-col">No</th>
                <th class="desc-col">Keterangan</th>
                <th class="amt-col">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($voucher->transactions as $idx => $tx)
                <tr class="item-row">
                    <td class="no-col">{{ $idx + 1 }}</td>
                    <td class="desc-col">{{ $tx->uraian }}</td>
                    <td class="amt-col">{{ number_format($tx->nominal, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            {{-- Filler blank rows to give a fixed table height (min 5 rows total) --}}
            @php $fillerCount = max(0, 5 - count($voucher->transactions)); @endphp
            @for($i = 0; $i < $fillerCount; $i++)
                <tr class="blank-row">
                    <td class="no-col">&nbsp;</td>
                    <td class="desc-col">&nbsp;</td>
                    <td class="amt-col">&nbsp;</td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="total-label">TOTAL</td>
                <td class="total-amount">Rp{{ number_format($voucher->total_nominal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- SIGNATURE SECTION --}}
    {{-- ══════════════════════════════════════════════════ --}}
    <table class="signature-wrapper">
        <tr>
            {{-- LEFT: Kasir | Ketua IV | Bendahara --}}
            <td>
                <table class="sig-left-table">
                    <thead>
                        <tr>
                            <th>Kasir</th>
                            <th>Ketua IV</th>
                            <th>Bendahara</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </td>

            {{-- RIGHT: Jakarta, [date] + Tanda Tangan Penerima --}}
            <td class="sig-right">
                <div class="city-line">
                    Jakarta, {{ \Carbon\Carbon::parse($voucher->tanggal)->translatedFormat('d F Y') }}
                </div>
                <div class="penerima-label">Tanda Tangan Penerima</div>
            </td>
        </tr>
    </table>

</body>
</html>
