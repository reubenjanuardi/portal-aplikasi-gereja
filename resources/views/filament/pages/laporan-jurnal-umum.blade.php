<x-filament-panels::page>
    <style>
        .report-section {
            margin-top: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .dark .report-section {
            border-color: rgba(255, 255, 255, 0.1);
            background: #18181b;
        }
        .report-section-header {
            padding: 14px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .dark .report-section-header {
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse !important;
            text-align: left;
            font-size: 13.5px;
        }
        table.data-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 10px 14px;
            border-bottom: 1px solid #cbd5e1;
        }
        .dark table.data-table th {
            background: rgba(255, 255, 255, 0.02);
            color: #9ca3af;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        table.data-table td {
            padding: 7px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #334155;
        }
        .dark table.data-table td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: #d1d5db;
        }
        table.data-table tfoot td {
            background: #f8fafc;
            font-weight: 700;
            padding: 14px;
            border-top: 2px solid #cbd5e1;
            color: #1e293b;
        }
        .dark table.data-table tfoot td {
            background: rgba(255, 255, 255, 0.02);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #e5e7eb;
        }
        .entry-group {
            border-bottom: 1px solid #e2e8f0;
        }
        .dark .entry-group {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .col-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .col-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 12px;
            font-weight: 700;
            color: #4f46e5;
            white-space: nowrap;
        }
        .dark .col-code {
            color: #818cf8;
        }
        .badge-counter {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .dark .badge-counter {
            background: rgba(255, 255, 255, 0.05);
            color: #9ca3af;
            border-color: rgba(255, 255, 255, 0.1);
        }
        .badge-masuk {
            display: inline-flex;
            align-items: center;
            padding: 2px 7px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .dark .badge-masuk {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border-color: rgba(52, 211, 153, 0.3);
        }
        .badge-keluar {
            display: inline-flex;
            align-items: center;
            padding: 2px 7px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }
        .dark .badge-keluar {
            background: rgba(244, 63, 94, 0.15);
            color: #fb7185;
            border-color: rgba(251, 113, 133, 0.3);
        }
        .badge-balanced {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .dark .badge-balanced {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border-color: rgba(52, 211, 153, 0.3);
        }
        .badge-unbalanced {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }
        .dark .badge-unbalanced {
            background: rgba(244, 63, 94, 0.15);
            color: #fb7185;
            border-color: rgba(251, 113, 133, 0.3);
        }
        .account-indent {
            padding-left: 28px !important;
        }
    </style>

    {{-- Form Filter Section --}}
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    {{-- Journal Table Section --}}
    <div class="report-section">
        <div class="report-section-header">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span class="text-base font-bold text-gray-900 dark:text-white">
                    Jurnal Umum (General Journal)
                </span>
                @if($this->isBalanced)
                    <span class="badge-balanced text-xs font-semibold px-2.5 py-0.5 rounded-md">
                        ✓ SEIMBANG (BALANCE)
                    </span>
                @else
                    <span class="badge-unbalanced text-xs font-semibold px-2.5 py-0.5 rounded-md">
                        ✕ TIDAK SEIMBANG
                    </span>
                @endif
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="badge-counter text-xs font-semibold px-2.5 py-1 rounded-md">
                    Total: {{ count($this->journalEntries) }} Transaksi
                </span>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 100px;">Tanggal</th>
                        <th style="width: 140px;">No. Bukti</th>
                        <th style="width: 80px; text-align: center;">Jenis</th>
                        <th style="width: 160px;">Pihak Terkait</th>
                        <th>Keterangan / Nama Rekening & Akun</th>
                        <th style="width: 120px; text-align: center;">Ref (Kode)</th>
                        <th style="width: 160px; text-align: right;">Debet (Rp)</th>
                        <th style="width: 160px; text-align: right;">Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->journalEntries as $entry)
                        @php
                            $isMasuk = in_array($entry['jenis_voucher'], ['Masuk', 'BKM', 'BBM'], true);
                        @endphp
                        {{-- Baris 1: Sisi Debet + Metadata Transaksi --}}
                        <tr style="background-color: #fdfdfd;" class="dark:bg-zinc-900/40">
                            <td class="text-gray-500 dark:text-gray-400 whitespace-nowrap font-medium align-top" rowspan="3" style="border-bottom: 2px solid #e2e8f0;">
                                {{ \Carbon\Carbon::parse($entry['tanggal'])->format('d/m/Y') }}
                            </td>
                            <td class="col-code align-top" rowspan="3" style="border-bottom: 2px solid #e2e8f0;">
                                {{ $entry['no_bukti'] }}
                            </td>
                            <td style="text-align: center; white-space: nowrap; align-top: top;" rowspan="3" style="border-bottom: 2px solid #e2e8f0;">
                                @if($isMasuk)
                                    <span class="badge-masuk">{{ $entry['jenis_voucher'] }}</span>
                                @else
                                    <span class="badge-keluar">{{ $entry['jenis_voucher'] }}</span>
                                @endif
                            </td>
                            <td class="text-gray-700 dark:text-gray-300 align-top" rowspan="3" style="border-bottom: 2px solid #e2e8f0;">
                                {{ $entry['pihak_terkait'] ?: '-' }}
                            </td>
                            {{-- Akun Debet --}}
                            <td class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $entry['debit']['nama_akun'] }}
                            </td>
                            <td class="col-code text-center font-mono">
                                {{ $entry['debit']['kode_akun'] }}
                            </td>
                            <td class="col-num font-semibold text-gray-900 dark:text-gray-100">
                                Rp {{ number_format($entry['debit']['nominal'], 0, ',', '.') }}
                            </td>
                            <td class="col-num text-gray-400">
                                -
                            </td>
                        </tr>

                        {{-- Baris 2: Sisi Kredit (Indented) --}}
                        <tr style="background-color: #fdfdfd;" class="dark:bg-zinc-900/40">
                            <td class="account-indent text-gray-700 dark:text-gray-300">
                                <span class="text-gray-400 mr-1">↳</span> {{ $entry['kredit']['nama_akun'] }}
                            </td>
                            <td class="col-code text-center font-mono">
                                {{ $entry['kredit']['kode_akun'] }}
                            </td>
                            <td class="col-num text-gray-400">
                                -
                            </td>
                            <td class="col-num font-semibold text-gray-900 dark:text-gray-100">
                                Rp {{ number_format($entry['kredit']['nominal'], 0, ',', '.') }}
                            </td>
                        </tr>

                        {{-- Baris 3: Catatan Uraian Transaksi --}}
                        <tr class="entry-group" style="background-color: #fcfcfc;" class="dark:bg-zinc-900/20">
                            <td colspan="4" style="font-size: 12px; font-style: italic; color: #64748b; padding-left: 20px; border-bottom: 2px solid #e2e8f0;">
                                ({{ $entry['uraian'] }})
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-400 dark:text-gray-500">
                                Tidak ada data jurnal umum untuk periode dan kriteria pencarian yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($this->journalEntries) > 0)
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-right uppercase text-xs tracking-wider text-gray-700 dark:text-gray-300 font-bold">
                                TOTAL DEBET & KREDIT:
                            </td>
                            <td class="col-num text-indigo-600 dark:text-indigo-400 text-base font-extrabold">
                                Rp {{ number_format($this->totalDebit, 0, ',', '.') }}
                            </td>
                            <td class="col-num text-indigo-600 dark:text-indigo-400 text-base font-extrabold">
                                Rp {{ number_format($this->totalKredit, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-filament-panels::page>
