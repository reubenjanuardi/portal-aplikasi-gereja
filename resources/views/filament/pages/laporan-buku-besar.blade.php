<x-filament-panels::page>
    <style>
        .report-section {
            margin-bottom: 24px;
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
            padding: 12px 16px;
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
        .coa-header-code {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .dark .coa-header-code {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border-color: rgba(96, 165, 250, 0.3);
        }
        .badge-masuk-summary {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .dark .badge-masuk-summary {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border-color: rgba(52, 211, 153, 0.3);
        }
        .badge-keluar-summary {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }
        .dark .badge-keluar-summary {
            background: rgba(244, 63, 94, 0.15);
            color: #fb7185;
            border-color: rgba(251, 113, 133, 0.3);
        }
        .badge-saldo-pos {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .dark .badge-saldo-pos {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border-color: rgba(96, 165, 250, 0.3);
        }
        .badge-saldo-neg {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .dark .badge-saldo-neg {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border-color: rgba(251, 191, 36, 0.3);
        }
        .badge-tipe {
            background: #f5f3ff;
            color: #6d28d9;
            border: 1px solid #ddd6fe;
        }
        .dark .badge-tipe {
            background: rgba(139, 92, 246, 0.15);
            color: #a78bfa;
            border-color: rgba(167, 139, 250, 0.3);
        }
        .summary-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
    </style>

    {{-- Form Filter Section --}}
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    {{-- Ringkasan Saldo Kas & Bank --}}
    @if($this->kasBankAccounts->isNotEmpty())
        <x-filament::section>
            <x-slot name="heading">Ringkasan Saldo Kas &amp; Bank</x-slot>
            <div class="summary-bar">
                @foreach($this->kasBankAccounts as $kasBank)
                    <span class="badge-tipe text-xs font-semibold px-2.5 py-1 rounded-md">
                        {{ $kasBank['kode_akun'] }} - {{ $kasBank['nama_akun'] }}
                    </span>
                    <span class="{{ $kasBank['saldo_akhir'] >= 0 ? 'badge-saldo-pos' : 'badge-saldo-neg' }} text-xs font-bold px-2.5 py-1 rounded-md">
                        Saldo: Rp {{ \App\Support\Format::nominal($kasBank['saldo_akhir']) }}
                    </span>
                @endforeach
                <span class="badge-saldo-pos text-xs font-bold px-2.5 py-1 rounded-md">
                    Total Saldo Kas &amp; Bank: Rp {{ \App\Support\Format::nominal($this->totalSaldoKasBank) }}
                </span>
            </div>
        </x-filament::section>
    @endif

    {{-- Data Section --}}
    <div style="margin-top: 20px;">
        @forelse($this->reportData as $account)
            @php
                $runningBalance = $account['saldo_awal'];
            @endphp

            <div class="report-section">
                <div class="report-section-header">
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span class="coa-header-code font-mono text-xs font-bold px-2 py-0.5 rounded-md">
                            {{ $account['kode_akun'] }}
                        </span>
                        <span class="text-base font-bold text-gray-900 dark:text-white">
                            {{ $account['nama_akun'] }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            ({{ $account['kategori'] ?? '-' }})
                        </span>
                        @if($account['is_kas_bank'])
                            <span class="badge-tipe text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wide">
                                Kas &amp; Bank
                            </span>
                        @endif
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span class="badge-masuk-summary text-xs font-semibold px-2.5 py-1 rounded-md">
                            Masuk: Rp {{ \App\Support\Format::nominal($account['total_masuk']) }}
                        </span>
                        <span class="badge-keluar-summary text-xs font-semibold px-2.5 py-1 rounded-md">
                            Keluar: Rp {{ \App\Support\Format::nominal($account['total_keluar']) }}
                        </span>
                        <span class="{{ $account['saldo_akhir'] >= 0 ? 'badge-saldo-pos' : 'badge-saldo-neg' }} text-xs font-bold px-2.5 py-1 rounded-md">
                            Saldo: Rp {{ \App\Support\Format::nominal($account['saldo_akhir']) }}
                        </span>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 130px;">No. Bukti</th>
                                <th style="width: 100px;">Tanggal</th>
                                <th style="width: 160px;">Pihak Terkait</th>
                                <th>Uraian</th>
                                <th style="width: 150px; text-align: right;">Debet</th>
                                <th style="width: 150px; text-align: right;">Kredit</th>
                                <th style="width: 150px; text-align: right;">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($account['saldo_awal'] != 0)
                                <tr>
                                    <td colspan="6" class="text-right text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 italic">
                                        Saldo awal per {{ \Carbon\Carbon::parse($this->startDate)->translatedFormat('d F Y') }}
                                    </td>
                                    <td class="col-num italic text-gray-500 dark:text-gray-400">
                                        {{ \App\Support\Format::nominal($account['saldo_awal']) }}
                                    </td>
                                </tr>
                            @endif

                            @foreach($account['lines'] as $line)
                                @php
                                    $runningBalance += $line['debit'] - $line['kredit'];
                                @endphp
                                <tr>
                                    <td class="col-code">{{ $line['no_bukti'] }}</td>
                                    <td class="text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($line['tanggal'])->format('d/m/Y') }}
                                    </td>
                                    <td class="text-gray-700 dark:text-gray-300">{{ $line['pihak_terkait'] ?? '-' }}</td>
                                    <td class="text-gray-800 dark:text-gray-200">
                                        {{ $line['uraian'] }}
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wide">
                                            ({{ $line['jenis_voucher'] }})
                                        </span>
                                    </td>
                                    <td class="col-num font-semibold {{ $line['debit'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-600' }}">
                                        {{ $line['debit'] > 0 ? \App\Support\Format::nominal($line['debit']) : '-' }}
                                    </td>
                                    <td class="col-num font-semibold {{ $line['kredit'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400 dark:text-gray-600' }}">
                                        {{ $line['kredit'] > 0 ? \App\Support\Format::nominal($line['kredit']) : '-' }}
                                    </td>
                                    <td class="col-num {{ $runningBalance < 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-700 dark:text-gray-300' }}">
                                        {{ \App\Support\Format::nominal($runningBalance) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right uppercase text-[11px] tracking-wider text-gray-700 dark:text-gray-300 font-bold">
                                    Subtotal {{ $account['kode_akun'] }}:
                                </td>
                                <td class="col-num text-emerald-600 dark:text-emerald-400 font-extrabold text-sm">
                                    {{ \App\Support\Format::nominal($account['total_masuk']) }}
                                </td>
                                <td class="col-num text-rose-600 dark:text-rose-400 font-extrabold text-sm">
                                    {{ \App\Support\Format::nominal($account['total_keluar']) }}
                                </td>
                                <td class="col-num text-gray-900 dark:text-white font-extrabold text-sm">
                                    {{ \App\Support\Format::nominal($account['saldo_akhir']) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-center py-10 px-5 bg-white dark:bg-[#18181b] border border-gray-200 dark:border-white/10 rounded-xl text-gray-400 dark:text-gray-500">
                Tidak ada transaksi yang ditemukan untuk periode &amp; filter yang dipilih.
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
