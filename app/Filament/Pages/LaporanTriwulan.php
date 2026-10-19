<?php

namespace App\Filament\Pages;

use App\Services\LaporanTriwulanService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

class LaporanTriwulan extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.laporan-triwulan';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static UnitEnum|string|null $navigationGroup = 'Laporan';

    protected static ?string $title = 'Laporan Triwulan';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('keuangan.laporan.view') ?? false;
    }

    public ?int $tahunAnggaran = null;

    public ?int $triwulan = null;

    public function mount(): void
    {
        $this->form->fill([
            'tahunAnggaran' => $this->tahunAnggaran,
            'triwulan' => $this->triwulan,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $years = app(LaporanTriwulanService::class)->availableFiscalYears();
        $quarters = [
            1 => 'Triwulan I (April–Juni)',
            2 => 'Triwulan II (Juli–September)',
            3 => 'Triwulan III (Oktober–Desember)',
            4 => 'Triwulan IV (Januari–Maret)',
        ];

        return $schema
            ->schema([
                Select::make('tahunAnggaran')
                    ->label('Tahun Anggaran')
                    ->options($years)
                    ->live()
                    ->placeholder('Pilih tahun anggaran'),

                Select::make('triwulan')
                    ->label('Triwulan')
                    ->options($quarters)
                    ->live()
                    ->placeholder('Pilih triwulan'),
            ])
            ->columns(2);
    }

    /**
     * Data laporan: rekapitulasi + detail bulanan.
     *
     * @return array<string, mixed>
     */
    public function getReportDataProperty(): array
    {
        $state = $this->form->getState();
        $tahunAnggaran = $state['tahunAnggaran'] ?? null;
        $triwulan = $state['triwulan'] ?? null;

        if (! $tahunAnggaran || ! $triwulan) {
            return [
                'period' => null,
                'rekap' => [
                    'penerimaan' => ['items' => [], 'total_anggaran' => 0, 'total_anggaran_triwulan' => 0, 'total_realisasi' => 0],
                    'pengeluaran' => ['items' => [], 'total_anggaran' => 0, 'total_anggaran_triwulan' => 0, 'total_realisasi' => 0],
                ],
                'detail' => [],
                'totalPenerimaan' => 0,
                'totalPengeluaran' => 0,
                'surplusDefisit' => 0,
            ];
        }

        return app(LaporanTriwulanService::class)->getReport($tahunAnggaran, $triwulan);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(function (): string {
                    $state = $this->form->getState();
                    $tahunAnggaran = $state['tahunAnggaran'] ?? '';
                    $triwulan = $state['triwulan'] ?? '';
                    return route('laporan.triwulan.excel', [
                        'tahunAnggaran' => $tahunAnggaran,
                        'triwulan' => $triwulan,
                    ]);
                })
                ->openUrlInNewTab()
                ->visible(function (): bool {
                    $state = $this->form->getState();
                    return !empty($state['tahunAnggaran']) && !empty($state['triwulan']);
                }),

            Action::make('cetak_pdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(function (): string {
                    $state = $this->form->getState();
                    $tahunAnggaran = $state['tahunAnggaran'] ?? '';
                    $triwulan = $state['triwulan'] ?? '';
                    return route('laporan.triwulan.pdf', [
                        'tahunAnggaran' => $tahunAnggaran,
                        'triwulan' => $triwulan,
                    ]);
                })
                ->openUrlInNewTab()
                ->visible(function (): bool {
                    $state = $this->form->getState();
                    return !empty($state['tahunAnggaran']) && !empty($state['triwulan']);
                }),
        ];
    }
}
