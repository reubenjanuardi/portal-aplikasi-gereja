<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Ringkasan Saldo Awal</x-slot>
        <x-slot name="description">
            Saldo awal menjadi titik awal perhitungan Buku Besar. Total Kas &amp; Bank di bawah otomatis menjadi pembuka saat membuka laporan.
        </x-slot>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-lg border border-gray-200 dark:border-white/10 p-4">
                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Periode</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->summary['periode'] }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 dark:border-white/10 p-4">
                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah Akun</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($this->summary['jumlah'], 0, ',', '.') }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 dark:border-white/10 p-4">
                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Saldo Awal</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($this->summary['total'], 0, ',', '.') }}</div>
            </div>
            <div class="rounded-lg border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/10 p-4">
                <div class="text-xs uppercase tracking-wide text-indigo-600 dark:text-indigo-300">Total Kas &amp; Bank</div>
                <div class="text-lg font-bold text-indigo-700 dark:text-indigo-200">Rp {{ number_format($this->summary['totalKas'], 0, ',', '.') }}</div>
            </div>
        </div>
    </x-filament::section>

    {{-- Form input manual --}}
    <x-filament::section collapsible collapsed>
        <x-slot name="heading">Tambah / Ubah Saldo Awal</x-slot>
        <x-slot name="description">Isi satu akun per satu. Simpan akan menimpa nilai bila akun + periode yang sama sudah ada.</x-slot>

        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button wire:click="saveAction" icon="heroicon-m-check">
                Simpan Saldo Awal
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- Import CSV --}}
    <x-filament::section collapsible collapsed>
        <x-slot name="heading">Import dari CSV</x-slot>
        <x-slot name="description">
            Cocok bila akun yang perlu diisi banyak. Unduh template, isi kolom saldo_awal, lalu salin dan tempel di bawah.
        </x-slot>

        <div class="mb-4">
            <x-filament::button color="success" icon="heroicon-m-arrow-down-tray" wire:click="downloadTemplateAction">
                Unduh Template CSV
            </x-filament::button>
        </div>

        {{ $this->importForm }}

        <div class="mt-4">
            <x-filament::button wire:click="importAction" icon="heroicon-m-arrow-up-tray">
                Proses Import
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- Tabel data --}}
    <x-filament::section>
        <x-slot name="heading">Daftar Saldo Awal</x-slot>
        <x-slot name="description">Saldo awal untuk periode yang sedang dipilih.</x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
