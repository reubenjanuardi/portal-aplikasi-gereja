<?php

namespace App\Filament\Pages;

use App\Models\ChartOfAccount;
use App\Models\OpeningBalance;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Writer;
use UnitEnum;

class SaldoAwal extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'filament.pages.saldo-awal';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-scale';

    protected static UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Saldo Awal';

    protected static ?string $title = 'Saldo Awal Buku Besar';

    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('keuangan.coa.manage') ?? false;
    }

    public ?string $periode = null;
    public ?string $kodeAkun = null;
    public ?string $saldoAwal = null;
    public ?string $keterangan = null;

    /** Batasi daftar akun ke Kas & Bank saja (buku kas & bank). */
    public bool $hanyaKasBank = true;

    /** Isi CSV yang ditempel user (alternatif unggah file). */
    public ?string $csvText = null;

    public function mount(): void
    {
        $this->periode = OpeningBalance::existingPeriods()[0]
            ?? now()->startOfYear()->toDateString();

        $this->form->fill([
            'periode' => $this->periode,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                DatePicker::make('periode')
                    ->label('Periode Saldo Awal')
                    ->required()
                    ->default(now()->startOfYear()->toDateString())
                    ->helperText('Contoh: 2026-07-01 berarti saldo awal per 1 Juli 2026.')
                    ->live(),

                Toggle::make('hanyaKasBank')
                    ->label('Hanya Akun Kas & Bank')
                    ->helperText('Matikan bila perlu mengisi saldo awal akun non-kas (piutang/hutang).')
                    ->default(true)
                    ->live(),

                Select::make('kodeAkun')
                    ->label('Kode Akun')
                    ->required()
                    ->searchable()
                    ->helperText('Daftar dibatasi ke akun Kas & Bank. Matikan sakelar di bawah untuk melihat seluruh akun postable.')
                    ->options(fn (): array => $this->coaOptions())
                    ->getSearchResultsUsing(fn (string $search): array => $this->coaOptions($search)),

                TextInput::make('saldoAwal')
                    ->label('Saldo Awal (Rp)')
                    ->required()
                    ->prefix('Rp')
                    ->helperText('Boleh memakai titik sebagai pemisah ribuan, contoh: 7.500.000 atau 7500000.'),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(4);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OpeningBalance::query()
                    ->with('chartOfAccount')
                    ->when($this->periode, fn ($q) => $q->whereDate('periode', $this->periode))
            )
            ->columns([
                TextColumn::make('kode_akun')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('chartOfAccount.nama_akun')
                    ->label('Nama Akun')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('chartOfAccount.kategori')
                    ->label('Kategori')
                    ->badge(),

                TextColumn::make('saldo_awal')
                    ->label('Saldo Awal')
                    ->alignEnd()
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(40)
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('kode_akun')
            ->paginated([10, 25, 50, 100]);
    }

    /**
     * Simpan / perbarui saldo awal untuk akun + periode terpilih.
     */
    public function saveAction(): void
    {
        $data = $this->form->getState();

        // Normalkan periode ke format Y-m-d supaya cocok dengan kolom `date`
        // saat dibandingkan oleh updateOrCreate.
        $periode = Carbon::parse($data['periode'])->toDateString();

        $exists = OpeningBalance::where('kode_akun', $data['kodeAkun'])
            ->whereDate('periode', $periode)
            ->first();

        OpeningBalance::updateOrCreate(
            [
                'kode_akun' => $data['kodeAkun'],
                'periode'   => $periode,
            ],
            [
                'saldo_awal' => $this->parseNominal($data['saldoAwal']),
                'keterangan' => $data['keterangan'] ?? null,
            ]
        );

        $exists
            ? Notification::make()->success()->title('Saldo Awal Diperbarui')->send()
            : Notification::make()->success()->title('Saldo Awal Disimpan')->send();

        $this->reset('kodeAkun', 'saldoAwal', 'keterangan');
        $this->form->fill(['periode' => $this->periode]);
    }

    public function deleteAction(int $id): void
    {
        OpeningBalance::find($id)?->delete();

        Notification::make()->success()->title('Saldo Awal Dihapus')->send();
    }

    /**
     * Unduh template CSV berisi seluruh akun postable beserta saldo awal
     * yang saat ini tercatat (0 bila belum diisi).
     */
    public function downloadTemplateAction(): mixed
    {
        $period = $this->periode ?: now()->startOfYear()->toDateString();

        $existing = OpeningBalance::mapForPeriod($period);

        $rows = ChartOfAccount::where('is_postable', true)
            ->when($this->hanyaKasBank, fn ($q) => $q->where('kategori', 'Kas & Bank'))
            ->orderBy('kode_akun')
            ->get()
            ->map(fn (ChartOfAccount $coa): array => [
                $coa->kode_akun,
                $coa->nama_akun,
                $coa->kategori,
                $existing[$coa->kode_akun] ?? 0,
            ])
            ->all();

        $csv = Writer::createFromString();
        $csv->insertOne(['kode_akun', 'nama_akun', 'kategori', 'saldo_awal']);
        $csv->insertAll($rows);

        $filename = 'saldo-awal-' . Carbon::parse($period)->toDateString() . '.csv';

        return response()->streamDownload(
            fn () => print($csv->toString()),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    /**
     * Import saldo awal dari CSV yang ditempel pada textarea.
     *
     * Kolom wajib pada baris pertama: kode_akun, saldo_awal.
     */
    public function importAction(): void
    {
        $this->validate([
            'csvText' => ['required', 'string', 'min:10'],
        ]);

        try {
            $csv = Reader::createFromString((string) $this->csvText);
            $csv->setHeaderOffset(0);
        } catch (\Throwable $e) {
            Notification::make()->danger()->title('Gagal membaca CSV')->body($e->getMessage())->send();
            return;
        }

        $headers = $csv->getHeader();

        if (! in_array('kode_akun', $headers, true) || ! in_array('saldo_awal', $headers, true)) {
            Notification::make()
                ->danger()
                ->title('Header CSV tidak sesuai')
                ->body('Pastikan baris pertama memuat kolom: kode_akun dan saldo_awal.')
                ->send();
            return;
        }

        $period = $this->periode ?: now()->startOfYear()->toDateString();

        $validCodes = ChartOfAccount::where('is_postable', true)->pluck('kode_akun')->flip();
        $imported = 0;
        $skipped = [];
        $values = [];

        foreach ($csv->getRecords() as $offset => $record) {
            $kode = trim((string) ($record['kode_akun'] ?? ''));

            if ($kode === '') {
                continue;
            }

            if (! $validCodes->has($kode)) {
                $skipped[] = "{$kode} (baris " . ($offset + 2) . ')';
                continue;
            }

            $nominal = $this->parseNominal($record['saldo_awal'] ?? '0');

            $values[$kode] = [
                'kode_akun'  => $kode,
                'periode'    => Carbon::parse($period)->toDateString(),
                'saldo_awal' => $nominal,
                'keterangan' => trim((string) ($record['keterangan'] ?? '')) ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $imported++;
        }

        if ($values !== []) {
            // Upsert per akun+periode agar aman dijalankan berulang kali.
            DB::transaction(function () use ($values) {
                foreach ($values as $kode => $row) {
                    OpeningBalance::updateOrCreate(
                        ['kode_akun' => $kode, 'periode' => $row['periode']],
                        ['saldo_awal' => $row['saldo_awal'], 'keterangan' => $row['keterangan']]
                    );
                }
            });
        }

        Notification::make()
            ->success()
            ->title("Import Berhasil: {$imported} akun")
            ->body($skipped === [] ? 'Semua baris valid.' : 'Baris dilewati: ' . implode(', ', array_slice($skipped, 0, 5)))
            ->send();

        $this->csvText = null;
    }

    /**
     * Daftar akun postable untuk dropdown, difilter dengan pencarian.
     *
     * @return array<string, string>
     */
    protected function coaOptions(?string $search = null): array
    {
        $query = ChartOfAccount::where('is_postable', true)
            ->when($this->hanyaKasBank, fn ($q) => $q->where('kategori', 'Kas & Bank'))
            ->orderBy('kode_akun');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_akun', 'like', "%{$search}%")
                  ->orWhere('nama_akun', 'like', "%{$search}%");
            });
        }

        return $query->limit(2000)
            ->get()
            ->mapWithKeys(fn (ChartOfAccount $coa) => [$coa->kode_akun => "{$coa->kode_akun} - {$coa->nama_akun}"])
            ->all();
    }

    public function getSummaryProperty(): array
    {
        $period = $this->periode ?: now()->startOfYear()->toDateString();
        $rows = OpeningBalance::query()
            ->with('chartOfAccount')
            ->whereDate('periode', $period)
            ->get();

        $kasBank = $rows->filter(fn (OpeningBalance $ob): bool => $ob->chartOfAccount?->kategori === 'Kas & Bank');

        return [
            'periode'   => Carbon::parse($period)->translatedFormat('d F Y'),
            'jumlah'    => $rows->count(),
            'total'     => (float) $rows->sum(fn (OpeningBalance $ob): float => (float) $ob->saldo_awal),
            'kasBank'   => $kasBank->count(),
            'totalKas'  => (float) $kasBank->sum(fn (OpeningBalance $ob): float => (float) $ob->saldo_awal),
        ];
    }

    /**
     * Ubah input nominal versi Indonesia menjadi float.
     *
     * Menerima "7.500.000", "7,500,000", "7500000", dan "-250000".
     * Pemisah ribuan dibuang; titik/koma desimal hanya
     * dipertahankan bila angka di belakang pemisah hanya 1-2 digit (mis. 1.500,5).
     */
    protected function parseNominal(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $raw = trim((string) $value);
        $negative = str_starts_with($raw, '-');

        // Pisahkan bagian desimal (paling belakang) bila Allow ini.
        if (preg_match('/^([\d.,]+)[.,](\d{1,2})$/', $raw, $m)) {
            $whole = str_replace(['.', ','], '', $m[1]);
            $result = (float) ($whole . '.' . $m[2]);
        } else {
            // Semua titik/koma diperlakukan sebagai pemisah ribuan.
            $result = (float) str_replace(['.', ','], '', preg_replace('/[^0-9.,]/', '', $raw));
        }

        return $negative ? -$result : $result;
    }

    public function importForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Textarea::make('csvText')
                    ->label('Isi CSV')
                    ->rows(10)
                    ->required()
                    ->placeholder("kode_akun,saldo_awal,keterangan\n111.02,5000000,Kas kecil awal\n112.02,15000000,Bank BRI")
                    ->helperText('Tempel isi file CSV di sini. Baris pertama harus berisi kolom kode_akun dan saldo_awal.')
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }
}
