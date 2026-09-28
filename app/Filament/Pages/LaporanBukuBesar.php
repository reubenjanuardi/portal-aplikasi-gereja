<?php

namespace App\Filament\Pages;

use App\Models\ChartOfAccount;
use App\Services\BukuBesarService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use UnitEnum;

class LaporanBukuBesar extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.laporan-buku-besar';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-book-open';

    protected static UnitEnum|string|null $navigationGroup = 'Laporan';

    protected static ?string $title = 'Buku Besar';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('keuangan.laporan.view') ?? false;
    }

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $kodeAkun = null;
    public ?string $jenisVoucher = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->endOfMonth()->toDateString();

        $this->form->fill([
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'kodeAkun' => $this->kodeAkun,
            'jenisVoucher' => $this->jenisVoucher,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                DatePicker::make('startDate')
                    ->label('Dari Tanggal')
                    ->required()
                    ->live(),

                DatePicker::make('endDate')
                    ->label('Sampai Tanggal')
                    ->required()
                    ->live(),

                Select::make('kodeAkun')
                    ->label('Kode Akun')
                    ->placeholder('-- Semua Akun --')
                    ->searchable()
                    ->options(
                        fn (): array => ChartOfAccount::where('is_postable', true)
                            ->orderBy('kode_akun')
                            ->get()
                            ->mapWithKeys(fn (ChartOfAccount $coa) => [$coa->kode_akun => "{$coa->kode_akun} - {$coa->nama_akun}"])
                            ->toArray()
                    )
                    ->helperText('Akun Kas & Bank (mis. 111.02 Kas Kecil) menampilkan saldo dari mutasi kas masuk & kas keluar.')
                    ->live(),

                Select::make('jenisVoucher')
                    ->label('Jenis Voucher')
                    ->placeholder('-- Semua --')
                    ->options([
                        'BKM' => 'Bukti Kas Masuk (BKM)',
                        'BKK' => 'Bukti Kas Keluar (BKK)',
                        'BBM' => 'Bukti Bank Masuk (BBM)',
                        'BBK' => 'Bukti Bank Keluar (BBK)',
                        'Masuk' => 'Masuk',
                        'Keluar' => 'Keluar',
                    ])
                    ->live(),
            ])
            ->columns(4);
    }

    /**
     * Data buku besar per akun (mata anggaran + akun Kas & Bank).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getReportDataProperty(): Collection
    {
        return app(BukuBesarService::class)
            ->getReportData(
                startDate: $this->startDate ?: now()->startOfMonth()->toDateString(),
                endDate: $this->endDate ?: now()->endOfMonth()->toDateString(),
                kodeAkun: $this->kodeAkun,
                jenisVoucher: $this->jenisVoucher,
            )['accounts'];
    }

    /**
     * Total saldo akhir seluruh akun Kas & Bank pada periode berjalan.
     */
    public function getTotalSaldoKasBankProperty(): float
    {
        return (float) $this->reportData
            ->where('is_kas_bank', true)
            ->sum('saldo_akhir');
    }

    /**
     * Ringkasan saldo akun Kas & Bank untuk ditampilkan di bagian atas laporan.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getKasBankAccountsProperty(): Collection
    {
        return $this->reportData
            ->where('is_kas_bank', true)
            ->values();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (): string => route('laporan.buku-besar.excel', $this->exportParams()))
                ->openUrlInNewTab(),

            Action::make('cetak_pdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn (): string => route('laporan.buku-besar.pdf', $this->exportParams()))
                ->openUrlInNewTab(),
        ];
    }

    /**
     * Parameter yang diteruskan ke route export PDF & Excel.
     *
     * @return array<string, string>
     */
    protected function exportParams(): array
    {
        return [
            'startDate' => $this->startDate ?? '',
            'endDate' => $this->endDate ?? '',
            'kodeAkun' => $this->kodeAkun ?? '',
            'jenisVoucher' => $this->jenisVoucher ?? '',
        ];
    }
}
