<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use App\Models\Voucher;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use BackedEnum;
use UnitEnum;
use Illuminate\Support\Collection;

class LaporanJurnalUmum extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.laporan-jurnal-umum';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static UnitEnum|string|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Jurnal Umum';

    protected static ?string $title = 'Laporan Jurnal Umum';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('keuangan.laporan.view') ?? false;
    }

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $jenisVoucher = null;
    public ?string $search = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->endOfMonth()->toDateString();

        $this->form->fill([
            'startDate'    => $this->startDate,
            'endDate'      => $this->endDate,
            'jenisVoucher' => $this->jenisVoucher,
            'search'       => $this->search,
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

                Select::make('jenisVoucher')
                    ->label('Jenis Voucher')
                    ->placeholder('-- Semua Jenis --')
                    ->options([
                        'BKM' => 'BKM (Kas Masuk)',
                        'BKK' => 'BKK (Kas Keluar)',
                        'BBM' => 'BBM (Bank Masuk)',
                        'BBK' => 'BBK (Bank Keluar)',
                    ])
                    ->live(),

                TextInput::make('search')
                    ->label('Cari Transaksi')
                    ->placeholder('No. bukti, pihak, atau uraian...')
                    ->live(debounce: 400),
            ])
            ->columns(4);
    }

    /**
     * Get paired double-entry journal items.
     */
    public function getJournalEntriesProperty(): Collection
    {
        $query = Transaction::query()
            ->with(['chartOfAccount', 'voucher.akunKasBank'])
            ->whereHas('voucher', function ($q) {
                if ($this->startDate) {
                    $q->where('tanggal', '>=', $this->startDate);
                }
                if ($this->endDate) {
                    $q->where('tanggal', '<=', $this->endDate);
                }
                if ($this->jenisVoucher) {
                    $q->where('jenis_voucher', $this->jenisVoucher);
                }
                if ($this->search) {
                    $term = '%' . $this->search . '%';
                    $q->where(function ($sub) use ($term) {
                        $sub->where('no_bukti', 'like', $term)
                            ->orWhere('pihak_terkait', 'like', $term);
                    });
                }
            });

        if ($this->search) {
            $term = '%' . $this->search . '%';
            $query->orWhere('uraian', 'like', $term);
        }

        $transactions = $query->orderBy(
            Voucher::select('tanggal')
                ->whereColumn('vouchers.no_bukti', 'transactions.no_bukti')
        )->get();

        return $transactions->map(function (Transaction $tx) {
            $voucher = $tx->voucher;
            $isKeluar = in_array($voucher?->jenis_voucher ?? '', ['Keluar', 'BKK', 'BBK'], true);
            $nominal = (float) $tx->nominal;

            $akunKasBank = $voucher?->akunKasBank;
            $akunMataAnggaran = $tx->chartOfAccount;

            if ($isKeluar) {
                // Pengeluaran: Debet Mata Anggaran, Kredit Kas/Bank
                $debit = [
                    'kode_akun' => $tx->kode_akun ?? '-',
                    'nama_akun' => $akunMataAnggaran?->nama_akun ?? 'Pos Anggaran',
                    'nominal'   => $nominal,
                ];
                $kredit = [
                    'kode_akun' => $voucher?->kode_akun_kas_bank ?? '-',
                    'nama_akun' => $akunKasBank?->nama_akun ?? 'Akun Kas / Bank',
                    'nominal'   => $nominal,
                ];
            } else {
                // Penerimaan: Debet Kas/Bank, Kredit Mata Anggaran
                $debit = [
                    'kode_akun' => $voucher?->kode_akun_kas_bank ?? '-',
                    'nama_akun' => $akunKasBank?->nama_akun ?? 'Akun Kas / Bank',
                    'nominal'   => $nominal,
                ];
                $kredit = [
                    'kode_akun' => $tx->kode_akun ?? '-',
                    'nama_akun' => $akunMataAnggaran?->nama_akun ?? 'Pos Anggaran',
                    'nominal'   => $nominal,
                ];
            }

            return [
                'id'            => $tx->id,
                'no_bukti'      => $tx->no_bukti,
                'tanggal'       => $voucher?->tanggal ?? now()->toDateString(),
                'jenis_voucher' => $voucher?->jenis_voucher ?? '-',
                'pihak_terkait' => $voucher?->pihak_terkait ?? '-',
                'uraian'        => $tx->uraian,
                'nominal'       => $nominal,
                'debit'         => $debit,
                'kredit'        => $kredit,
            ];
        });
    }

    public function getTotalDebitProperty(): float
    {
        return (float) $this->journalEntries->sum('nominal');
    }

    public function getTotalKreditProperty(): float
    {
        return (float) $this->journalEntries->sum('nominal');
    }

    public function getIsBalancedProperty(): bool
    {
        return round($this->totalDebit, 2) === round($this->totalKredit, 2);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (): string => route('laporan.jurnal-umum.excel', [
                    'startDate'    => $this->startDate ?? '',
                    'endDate'      => $this->endDate ?? '',
                    'jenisVoucher' => $this->jenisVoucher ?? '',
                    'search'       => $this->search ?? '',
                ]))
                ->openUrlInNewTab(),

            Action::make('cetak_pdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn (): string => route('laporan.jurnal-umum.pdf', [
                    'startDate'    => $this->startDate ?? '',
                    'endDate'      => $this->endDate ?? '',
                    'jenisVoucher' => $this->jenisVoucher ?? '',
                    'search'       => $this->search ?? '',
                ]))
                ->openUrlInNewTab(),
        ];
    }
}
