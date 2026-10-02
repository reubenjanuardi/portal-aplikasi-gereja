<x-filament-panels::page>
    <style>
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .dark .kpi-card {
            background: #18181b;
            border-color: rgba(255, 255, 255, 0.1);
        }
        .kpi-icon-box {
            width: 36px !important;
            height: 36px !important;
            min-width: 36px !important;
            min-height: 36px !important;
            max-width: 36px !important;
            max-height: 36px !important;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .kpi-icon-box svg {
            width: 20px !important;
            height: 20px !important;
            min-width: 20px !important;
            min-height: 20px !important;
            max-width: 20px !important;
            max-height: 20px !important;
            display: block;
        }
        .kpi-icon-penerimaan { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .dark .kpi-icon-penerimaan { background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(52, 211, 153, 0.3); }

        .kpi-icon-pengeluaran { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }
        .dark .kpi-icon-pengeluaran { background: rgba(244, 63, 94, 0.15); color: #fb7185; border-color: rgba(251, 113, 133, 0.3); }

        .kpi-icon-surplus { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .dark .kpi-icon-surplus { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border-color: rgba(96, 165, 250, 0.3); }

        .kpi-icon-defisit { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .dark .kpi-icon-defisit { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: rgba(251, 191, 36, 0.3); }

        .kpi-icon-saldo { background: #eef2ff; color: #4f46e5; border: 1px solid #c7d2fe; }
        .dark .kpi-icon-saldo { background: rgba(99, 102, 241, 0.15); color: #818cf8; border-color: rgba(129, 140, 248, 0.3); }

        .report-table-wrapper {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .dark .report-table-wrapper {
            border-color: rgba(255, 255, 255, 0.1);
            background: #18181b;
        }
        .report-table-header {
            padding: 14px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            background: #f8fafc;
        }
        .dark .report-table-header {
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        table.report-table {
            width: 100%;
            border-collapse: collapse !important;
            text-align: left;
            font-size: 13.5px;
        }
        table.report-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 10px 16px;
            border-bottom: 1px solid #cbd5e1;
        }
        .dark table.report-table th {
            background: rgba(255, 255, 255, 0.02);
            color: #9ca3af;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        table.report-table td {
            padding: 9px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #334155;
        }
        .dark table.report-table td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: #d1d5db;
        }
        table.report-table tr:hover td {
            background-color: #f8fafc;
        }
        .dark table.report-table tr:hover td {
            background-color: rgba(255, 255, 255, 0.03);
        }
        table.report-table tr.row-depth-0 td {
            background-color: #f1f5f9 !important;
            font-weight: 700;
            color: #0f172a !important;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
        }
        .dark table.report-table tr.row-depth-0 td {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #f9fafb !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        table.report-table tr.row-depth-1 td {
            background-color: #f8fafc !important;
            font-weight: 600;
            color: #1e293b !important;
        }
        .dark table.report-table tr.row-depth-1 td {
            background-color: rgba(255, 255, 255, 0.02) !important;
            color: #e5e7eb !important;
        }
        table.report-table tfoot td {
            background: #f1f5f9;
            font-weight: 700;
            padding: 12px 16px;
            border-top: 2px solid #cbd5e1;
            color: #1e293b;
        }
        .dark table.report-table tfoot td {
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
            white-space: nowrap;
            color: #64748b;
        }
        .dark .col-code {
            color: #9ca3af;
        }
        .tab-btn {
            padding: 8px 16px;
            font-size: 13.5px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.15s ease;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .tab-btn-active {
            background: #4f46e5;
            color: #ffffff;
            box-shadow: 0 1px 2px rgba(79, 70, 229, 0.25);
        }
        .tab-btn-inactive {
            background: #f1f5f9;
            color: #475569;
        }
        .tab-btn-inactive:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        .dark .tab-btn-inactive {
            background: rgba(255, 255, 255, 0.05);
            color: #9ca3af;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .dark .tab-btn-inactive:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #f9fafb;
        }

        .report-badge-penerimaan {
            font-size: 13px;
            font-weight: 700;
            background: #ecfdf5;
            color: #047857;
            padding: 4px 12px;
            border-radius: 6px;
            border: 1px solid #a7f3d0;
        }
        .dark .report-badge-penerimaan {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border-color: rgba(52, 211, 153, 0.3);
        }

        .report-badge-pengeluaran {
            font-size: 13px;
            font-weight: 700;
            background: #fff1f2;
            color: #be123c;
            padding: 4px 12px;
            border-radius: 6px;
            border: 1px solid #fecdd3;
        }
        .dark .report-badge-pengeluaran {
            background: rgba(244, 63, 94, 0.15);
            color: #fb7185;
            border-color: rgba(251, 113, 133, 0.3);
        }

        .report-badge-saldo-awal {
            font-size: 12px;
            font-weight: 600;
            background: #f1f5f9;
            color: #334155;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }
        .dark .report-badge-saldo-awal {
            background: rgba(255, 255, 255, 0.06);
            color: #d1d5db;
            border-color: rgba(255, 255, 255, 0.1);
        }

        .report-badge-saldo-akhir {
            font-size: 12px;
            font-weight: 700;
            background: #eef2ff;
            color: #4338ca;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid #c7d2fe;
        }
        .dark .report-badge-saldo-akhir {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            border-color: rgba(129, 140, 248, 0.3);
        }
    </style>

    {{-- Form Filter Periode Section --}}
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    @php
        $data = $this->reportData;
        $totalPenerimaan = $data['totalPenerimaan'] ?? 0;
        $totalPengeluaran = $data['totalPengeluaran'] ?? 0;
        $surplusDefisit = $data['surplusDefisit'] ?? 0;
        $totalSaldoAwal = $data['totalSaldoAwal'] ?? 0;
        $totalSaldoAkhir = $data['totalSaldoAkhir'] ?? 0;
        $penerimaan = $data['penerimaan'] ?? [];
        $pengeluaran = $data['pengeluaran'] ?? [];
        $kasBank = $data['kasBank'] ?? [];
    @endphp

    {{-- Ringkasan KPI Cards --}}
    <div class="kpi-grid">
        {{-- Card 1: Penerimaan --}}
        <div class="kpi-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Total Penerimaan
                </span>
                <div class="kpi-icon-box kpi-icon-penerimaan">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 4.5-15 15m0 0h11.25m-11.25 0V8.25" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-[22px] font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">
                Rp {{ \App\Support\Format::nominal($totalPenerimaan) }}
            </div>
            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                Realisasi periode berjalan
            </div>
        </div>

        {{-- Card 2: Pengeluaran --}}
        <div class="kpi-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Total Pengeluaran
                </span>
                <div class="kpi-icon-box kpi-icon-pengeluaran">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-[22px] font-extrabold text-rose-600 dark:text-rose-400 font-mono">
                Rp {{ \App\Support\Format::nominal($totalPengeluaran) }}
            </div>
            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                Realisasi periode berjalan
            </div>
        </div>

        {{-- Card 3: Surplus / Defisit --}}
        <div class="kpi-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    {{ $surplusDefisit >= 0 ? 'Surplus Mingguan' : 'Defisit Mingguan' }}
                </span>
                <div class="kpi-icon-box {{ $surplusDefisit >= 0 ? 'kpi-icon-surplus' : 'kpi-icon-defisit' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-16.5 0c-.99.203-1.99.377-3 .52m16.5 0v5.334c0 .9-.54 1.71-1.37 2.05-1.07.44-2.2.77-3.38.98m-11.75-8.364v5.334c0 .9.54 1.71 1.37 2.05 1.07.44 2.2.77 3.38.98m0 0a24.28 24.28 0 0 0 10.25 0" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-[22px] font-extrabold {{ $surplusDefisit >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400' }} font-mono">
                Rp {{ \App\Support\Format::nominal($surplusDefisit) }}
            </div>
            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                Penerimaan - Pengeluaran
            </div>
        </div>

        {{-- Card 4: Saldo Akhir --}}
        <div class="kpi-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Total Saldo Akhir
                </span>
                <div class="kpi-icon-box kpi-icon-saldo">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v10.5m0-10.5h19.5m0 0v10.5m0-10.5c.727 0 1.453.198 1.453.75v.75m-1.453-.75H3.75m18 0v10.5m0 0a60.07 60.07 0 0 1-15.797 2.101c-.727.198-1.453-.342-1.453-1.096V18.75" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-[22px] font-extrabold text-indigo-600 dark:text-indigo-400 font-mono">
                Rp {{ \App\Support\Format::nominal($totalSaldoAkhir) }}
            </div>
            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                Posisi Kas & Bank per akhir periode
            </div>
        </div>
    </div>

    {{-- Tab Filter Button Bar --}}
    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px;" class="dark:border-gray-800">
        <button
            type="button"
            wire:click="setTab('all')"
            class="tab-btn {{ $activeTab === 'all' ? 'tab-btn-active' : 'tab-btn-inactive' }}"
        >
            Semua Bagian
        </button>
        <button
            type="button"
            wire:click="setTab('penerimaan')"
            class="tab-btn {{ $activeTab === 'penerimaan' ? 'tab-btn-active' : 'tab-btn-inactive' }}"
        >
            1. Penerimaan ({{ count($penerimaan) }})
        </button>
        <button
            type="button"
            wire:click="setTab('pengeluaran')"
            class="tab-btn {{ $activeTab === 'pengeluaran' ? 'tab-btn-active' : 'tab-btn-inactive' }}"
        >
            2. Pengeluaran ({{ count($pengeluaran) }})
        </button>
        <button
            type="button"
            wire:click="setTab('kas_bank')"
            class="tab-btn {{ $activeTab === 'kas_bank' ? 'tab-btn-active' : 'tab-btn-inactive' }}"
        >
            3. Saldo Kas & Bank ({{ count($kasBank) }})
        </button>
    </div>

    {{-- BAGIAN 1: PENERIMAAN --}}
    @if($activeTab === 'all' || $activeTab === 'penerimaan')
        <div class="report-table-wrapper">
            <div class="report-table-header">
                <div>
                    <div class="text-[15px] font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; background: #059669; color: #ffffff; font-size: 11px; font-weight: 800;">1</span>
                        Buku Pembantu Realisasi Mingguan - PENERIMAAN
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Periode: {{ \Carbon\Carbon::parse($this->startDate)->format('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($this->endDate)->format('d/m/Y') }}
                        @if($this->mingguKe) (Minggu ke-{{ $this->mingguKe }}) @endif
                    </div>
                </div>
                <div class="report-badge-penerimaan">
                    Total: Rp {{ \App\Support\Format::nominal($totalPenerimaan) }}
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="width: 150px;">No. MA</th>
                            <th>Mata Anggaran</th>
                            <th style="width: 220px; text-align: right;">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penerimaan as $row)
                            @php
                                $isGroup = $row['is_group'];
                                $depth = $row['depth'];
                                $paddingLeft = ($depth * 20) + 16;
                            @endphp
                            <tr class="row-depth-{{ $depth }}">
                                <td class="col-code">{{ $row['kode_akun'] }}</td>
                                <td style="padding-left: {{ $paddingLeft }}px;">
                                    @if(!$isGroup)
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-gray-500 mr-2 align-middle"></span>
                                    @endif
                                    <span>{{ $row['nama_akun'] }}</span>
                                </td>
                                <td class="col-num {{ $row['amount'] > 0 ? 'font-bold text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-600' }}">
                                    {{ \App\Support\Format::nominal($row['amount']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 30px;" class="text-gray-400 dark:text-gray-500">
                                    Tidak ada mata anggaran penerimaan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right uppercase tracking-wider text-xs text-gray-700 dark:text-gray-300 font-bold">
                                TOTAL PENERIMAAN:
                            </td>
                            <td class="col-num text-emerald-600 dark:text-emerald-400 text-[15px] font-extrabold">
                                Rp {{ \App\Support\Format::nominal($totalPenerimaan) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    {{-- BAGIAN 2: PENGELUARAN --}}
    @if($activeTab === 'all' || $activeTab === 'pengeluaran')
        <div class="report-table-wrapper">
            <div class="report-table-header">
                <div>
                    <div class="text-[15px] font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; background: #e11d48; color: #ffffff; font-size: 11px; font-weight: 800;">2</span>
                        Buku Pembantu Realisasi Mingguan - PENGELUARAN
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Periode: {{ \Carbon\Carbon::parse($this->startDate)->format('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($this->endDate)->format('d/m/Y') }}
                        @if($this->mingguKe) (Minggu ke-{{ $this->mingguKe }}) @endif
                    </div>
                </div>
                <div class="report-badge-pengeluaran">
                    Total: Rp {{ \App\Support\Format::nominal($totalPengeluaran) }}
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="width: 150px;">No. MA</th>
                            <th>Mata Anggaran</th>
                            <th style="width: 220px; text-align: right;">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengeluaran as $row)
                            @php
                                $isGroup = $row['is_group'];
                                $depth = $row['depth'];
                                $paddingLeft = ($depth * 20) + 16;
                            @endphp
                            <tr class="row-depth-{{ $depth }}">
                                <td class="col-code">{{ $row['kode_akun'] }}</td>
                                <td style="padding-left: {{ $paddingLeft }}px;">
                                    @if(!$isGroup)
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-gray-500 mr-2 align-middle"></span>
                                    @endif
                                    <span>{{ $row['nama_akun'] }}</span>
                                </td>
                                <td class="col-num {{ $row['amount'] > 0 ? 'font-bold text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-600' }}">
                                    {{ \App\Support\Format::nominal($row['amount']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 30px;" class="text-gray-400 dark:text-gray-500">
                                    Tidak ada mata anggaran pengeluaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right uppercase tracking-wider text-xs text-gray-700 dark:text-gray-300 font-bold">
                                TOTAL PENGELUARAN:
                            </td>
                            <td class="col-num text-rose-600 dark:text-rose-400 text-[15px] font-extrabold">
                                Rp {{ \App\Support\Format::nominal($totalPengeluaran) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    {{-- BAGIAN 3: SALDO KAS & BANK --}}
    @if($activeTab === 'all' || $activeTab === 'kas_bank')
        <div class="report-table-wrapper">
            <div class="report-table-header">
                <div>
                    <div class="text-[15px] font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; background: #4f46e5; color: #ffffff; font-size: 11px; font-weight: 800;">3</span>
                        Posisi Saldo Kas & Bank
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Saldo Awal (sebelum {{ \Carbon\Carbon::parse($this->startDate)->format('d/m/Y') }}) vs Saldo Akhir (per {{ \Carbon\Carbon::parse($this->endDate)->format('d/m/Y') }})
                    </div>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <span class="report-badge-saldo-awal">
                        Saldo Awal: Rp {{ \App\Support\Format::nominal($totalSaldoAwal) }}
                    </span>
                    <span class="report-badge-saldo-akhir">
                        Saldo Akhir: Rp {{ \App\Support\Format::nominal($totalSaldoAkhir) }}
                    </span>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="width: 150px;">No. MA</th>
                            <th>Mata Anggaran</th>
                            <th style="width: 200px; text-align: right;">A. Saldo Awal (Rp)</th>
                            <th style="width: 200px; text-align: right;">B. Saldo Akhir (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kasBank as $row)
                            @php
                                $isGroup = $row['is_group'];
                                $depth = $row['depth'];
                                $paddingLeft = ($depth * 20) + 16;
                            @endphp
                            <tr class="row-depth-{{ $depth }}">
                                <td class="col-code">{{ $row['kode_akun'] }}</td>
                                <td style="padding-left: {{ $paddingLeft }}px;">
                                    @if(!$isGroup)
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-gray-500 mr-2 align-middle"></span>
                                    @endif
                                    <span>{{ $row['nama_akun'] }}</span>
                                </td>
                                <td class="col-num {{ $row['saldo_awal'] != 0 ? 'font-bold text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-600' }}">
                                    {{ \App\Support\Format::nominal($row['saldo_awal']) }}
                                </td>
                                <td class="col-num {{ $row['saldo_akhir'] != 0 ? 'font-bold text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-600' }}">
                                    {{ \App\Support\Format::nominal($row['saldo_akhir']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 30px;" class="text-gray-400 dark:text-gray-500">
                                    Tidak ada akun Kas & Bank.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right uppercase tracking-wider text-xs text-gray-700 dark:text-gray-300 font-bold">
                                TOTAL SALDO KAS & BANK:
                            </td>
                            <td class="col-num text-sm font-bold text-gray-700 dark:text-gray-300">
                                Rp {{ \App\Support\Format::nominal($totalSaldoAwal) }}
                            </td>
                            <td class="col-num text-sm font-extrabold text-indigo-600 dark:text-indigo-400">
                                Rp {{ \App\Support\Format::nominal($totalSaldoAkhir) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>
