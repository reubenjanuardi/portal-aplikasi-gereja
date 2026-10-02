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
            padding: 10px 16px;
            border-bottom: 1px solid #cbd5e1;
        }
        .dark table.data-table th {
            background: rgba(255, 255, 255, 0.02);
            color: #9ca3af;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        table.data-table td {
            padding: 9px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #334155;
        }
        .dark table.data-table td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: #d1d5db;
        }
        table.data-table tr:hover td {
            background-color: #f8fafc;
        }
        .dark table.data-table tr:hover td {
            background-color: rgba(255, 255, 255, 0.03);
        }
        table.data-table tfoot td {
            background: #f8fafc;
            font-weight: 700;
            padding: 14px 16px;
            border-top: 2px solid #cbd5e1;
            color: #1e293b;
        }
        .dark table.data-table tfoot td {
            background: rgba(255, 255, 255, 0.02);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #e5e7eb;
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
            padding: 2px 8px;
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
            padding: 2px 8px;
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
    </style>

    {{-- Form Filter Section --}}
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    {{-- Journal Table Section --}}
    <div class="report-section">
        <div class="report-section-header">
            <span class="text-base font-bold text-gray-900 dark:text-white">
                Laporan Jurnal Transaksi
            </span>
            <span class="badge-counter text-xs font-semibold px-2.5 py-1 rounded-md">
                Total: {{ count($this->reportData) }} Baris Transaksi
            </span>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 100px;">Tanggal</th>
                        <th style="width: 140px;">No. Bukti</th>
                        <th style="width: 90px; text-align: center;">Jenis</th>
                        <th style="width: 180px;">Pihak Terkait</th>
                        <th style="width: 240px;">Akun Anggaran</th>
                        <th>Uraian</th>
                        <th style="width: 160px; text-align: right;">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotal = 0; @endphp
                    @forelse($this->reportData as $tx)
                        @php
                            $grandTotal += $tx->nominal;
                            $isMasuk = in_array($tx->voucher->jenis_voucher ?? '', ['Masuk', 'BKM', 'BBM'], true);
                        @endphp
                        <tr>
                            <td class="text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($tx->voucher->tanggal ?? now())->format('d/m/Y') }}
                            </td>
                            <td class="col-code">{{ $tx->no_bukti }}</td>
                            <td style="text-align: center; white-space: nowrap;">
                                @if($isMasuk)
                                    <span class="badge-masuk">{{ in_array($tx->voucher->jenis_voucher ?? '', ['BKM', 'BBM']) ? $tx->voucher->jenis_voucher : 'Masuk' }}</span>
                                @else
                                    <span class="badge-keluar">{{ in_array($tx->voucher->jenis_voucher ?? '', ['BKK', 'BBK']) ? $tx->voucher->jenis_voucher : 'Keluar' }}</span>
                                @endif
                            </td>
                            <td class="text-gray-700 dark:text-gray-300">{{ $tx->voucher->pihak_terkait ?? '-' }}</td>
                            <td>
                                <span class="font-mono text-xs font-bold text-gray-900 dark:text-white">{{ $tx->kode_akun }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">- {{ $tx->chartOfAccount->nama_akun ?? '-' }}</span>
                            </td>
                            <td class="text-gray-800 dark:text-gray-200">{{ $tx->uraian }}</td>
                            <td class="col-num font-bold text-gray-900 dark:text-gray-100">
                                Rp {{ \App\Support\Format::nominal($tx->nominal) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-gray-400 dark:text-gray-500">
                                Tidak ada transaksi yang ditemukan untuk periode dan filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($this->reportData) > 0)
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-right uppercase text-xs tracking-wider text-gray-700 dark:text-gray-300 font-bold">
                                GRAND TOTAL NOMINAL:
                            </td>
                            <td class="col-num text-indigo-600 dark:text-indigo-400 text-base font-extrabold">
                                Rp {{ \App\Support\Format::nominal($grandTotal) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-filament-panels::page>
