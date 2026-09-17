<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Models\ChartOfAccount;
use App\Models\Voucher;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static UnitEnum|string|null $navigationGroup = 'Transaksi';

    protected static ?string $recordTitleAttribute = 'no_bukti';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['akunKasBank', 'chartOfAccount']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('no_bukti')
                ->label('No Bukti')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->rule('regex:/^[^\s,]+$/')
                ->validationMessages([
                    'regex' => 'Format Nomor Bukti harus kontinu (menyambung) dan tidak boleh mengandung spasi atau tanda koma (,).',
                    'unique' => 'Nomor Bukti sudah digunakan.',
                ])
                ->default(fn (Get $get): string => static::generateNoBukti($get('jenis_voucher') ?? 'BKM', $get('tanggal') ?? now()->toDateString()))
                ->disabled()
                ->dehydrated(fn (string $operation): bool => $operation === 'create')
                ->prefixIcon('heroicon-m-lock-closed')
                ->extraInputAttributes([
                    'class' => 'bg-gray-100 dark:bg-gray-800 font-mono font-bold text-gray-700 dark:text-gray-300 cursor-not-allowed select-none',
                ])
                ->helperText('Nomor bukti dibuat otomatis oleh sistem dan tidak dapat diubah secara manual.')
                ->dehydrateStateUsing(fn (?string $state) => $state ? str_replace([',', ' '], ['', ''], $state) : $state),

            DatePicker::make('tanggal')
                ->required()
                ->default(fn (): string => now()->toDateString())
                ->live()
                ->afterStateUpdated(function (?string $state, Set $set, Get $get, string $operation): void {
                    if ($operation === 'create' && $state) {
                        $set('no_bukti', static::generateNoBukti($get('jenis_voucher') ?? 'BKM', $state));
                    }
                }),

            TextInput::make('pihak_terkait')
                ->label('Pihak Terkait')
                ->required()
                ->maxLength(255),

            Select::make('jenis_voucher')
                ->label('Jenis Voucher')
                ->required()
                ->default('BKM')
                ->options([
                    'BKM' => 'Bukti Kas Masuk (BKM)',
                    'BKK' => 'Bukti Kas Keluar (BKK)',
                    'BBM' => 'Bukti Bank Masuk (BBM)',
                    'BBK' => 'Bukti Bank Keluar (BBK)',
                ])
                ->live()
                ->afterStateUpdated(function (?string $state, Set $set, Get $get, string $operation): void {
                    if ($operation === 'create' && $state) {
                        $set('no_bukti', static::generateNoBukti($state, $get('tanggal') ?? now()->toDateString()));
                    }
                }),

            // ─── CoA 1: Akun Kas / Rekening Bank ──────────────────────────────────
            // Menentukan dari mana kas keluar atau ke mana kas/bank masuk.
            Select::make('kode_akun_kas_bank')
                ->label(fn (Get $get): string => match ($get('jenis_voucher')) {
                    'BKK' => 'Sumber Kas (Kas Keluar dari)',
                    'BBK' => 'Sumber Rekening Bank (Bank Keluar dari)',
                    'BKM' => 'Kas Penerima (Kas Masuk ke)',
                    'BBM' => 'Rekening Bank Penerima (Bank Masuk ke)',
                    default => 'Akun Kas / Rekening Bank',
                })
                ->required()
                ->searchable()
                ->preload()
                ->options(fn (Get $get, ?Voucher $record): array => static::getKasBankOptions(
                    $get('jenis_voucher'),
                    $record?->kode_akun_kas_bank
                ))
                ->helperText(fn (Get $get): string => match ($get('jenis_voucher')) {
                    'BKK' => 'Pilih akun kas tunai tempat dana dikeluarkan (misal: Kas Besar / Kas Kecil).',
                    'BBK' => 'Pilih rekening bank sumber penarikan atau transfer keluar.',
                    'BKM' => 'Pilih akun kas tunai tempat penerimaan uang.',
                    'BBM' => 'Pilih rekening bank penerima transfer/setoran masuk.',
                    default => 'Pilih akun kas atau rekening bank yang digunakan.',
                }),

            // ─── CoA 2: Mata Anggaran (Pos Beban / Penerimaan) ─────────────────────
            // Menentukan ke mana kas keluar (beban) atau dari mana penerimaan diperoleh.
            Select::make('kode_akun')
                ->label(fn (Get $get): string => match ($get('jenis_voucher')) {
                    'BKK', 'BBK' => 'Mata Anggaran Pengeluaran (Tujuan Pengeluaran)',
                    'BKM', 'BBM' => 'Mata Anggaran Penerimaan (Sumber Penerimaan)',
                    default => 'Mata Anggaran (Kode Akun)',
                })
                ->required()
                ->searchable()
                ->preload()
                ->optionsLimit(1000)
                ->options(fn (Get $get, ?Voucher $record): array => static::getMataAnggaranTreeOptions(
                    $get('jenis_voucher'),
                    $record?->kode_akun
                ))
                ->disableOptionWhen(function (?string $value): bool {
                    if (! $value) return false;
                    static $nonPostable = null;
                    if ($nonPostable === null) {
                        $nonPostable = ChartOfAccount::where('is_postable', false)->pluck('kode_akun')->flip()->toArray();
                    }
                    return isset($nonPostable[$value]);
                })
                ->helperText(fn (Get $get): string => match ($get('jenis_voucher')) {
                    'BKK' => 'Pilih pos mata anggaran pengeluaran, akun kas & bank (untuk setor kas ke bank), atau hutang/piutang.',
                    'BKM' => 'Pilih pos mata anggaran penerimaan, akun kas & bank, atau hutang/piutang.',
                    'BBK', 'BBM' => 'Pilih mata anggaran yang sesuai (semua kategori akun dapat dipilih).',
                    default => 'Semua baris item dalam voucher ini akan dicatat pada mata anggaran yang sama.',
                }),

            // ─── Detail Item ──────────────────────────────────────────────────────
            Repeater::make('transactions')
                ->label('Detail Item Transaksi')
                ->relationship()
                ->minItems(1)
                ->addActionLabel('Tambah Item')
                ->mutateRelationshipDataBeforeCreateUsing(function (array $data, Repeater $component): array {
                    $kodeAkun = $component->getRecord()?->kode_akun
                        ?? data_get($component->getLivewire(), 'data.kode_akun');
                    $data['kode_akun'] = $kodeAkun;
                    return $data;
                })
                ->mutateRelationshipDataBeforeSaveUsing(function (array $data, Repeater $component): array {
                    $kodeAkun = $component->getRecord()?->kode_akun
                        ?? data_get($component->getLivewire(), 'data.kode_akun');
                    if ($kodeAkun) {
                        $data['kode_akun'] = $kodeAkun;
                    }
                    return $data;
                })
                ->schema([
                    TextInput::make('uraian')
                        ->label('Keterangan / Uraian')
                        ->required()
                        ->maxLength(1000)
                        ->columnSpan(2),

                    TextInput::make('nominal')
                        ->required()
                        ->numeric()
                        ->inputMode('decimal')
                        ->prefix('Rp')
                        ->live(onBlur: true),
                ])
                ->columns(3),

            Placeholder::make('total_nominal')
                ->label('Total Nominal')
                ->content(function (Get $get): string {
                    $transactions = $get('transactions') ?? [];
                    $total = static::calculateTotalNominal($transactions);
                    return 'Rp ' . number_format($total, 0, ',', '.');
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->columns([
                TextColumn::make('no_bukti')
                    ->label('No Bukti')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('pihak_terkait')
                    ->label('Pihak Terkait')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis_voucher')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'BKM' => 'BKM (Kas Masuk)',
                        'BKK' => 'BKK (Kas Keluar)',
                        'BBM' => 'BBM (Bank Masuk)',
                        'BBK' => 'BBK (Bank Keluar)',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Masuk', 'BKM', 'BBM' => 'success',
                        'Keluar', 'BKK', 'BBK' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('akunKasBank.nama_akun')
                    ->label('Kas / Bank')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Voucher $record): ?string => $record->kode_akun_kas_bank),

                TextColumn::make('chartOfAccount.nama_akun')
                    ->label('Mata Anggaran')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Voucher $record): ?string => $record->kode_akun),

                TextColumn::make('total_nominal')
                    ->label('Total Nominal')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
            ])
            ->filters([
                SelectFilter::make('kode_akun_kas_bank')
                    ->label('Kas / Rekening Bank')
                    ->options(fn (): array => static::getKasBankOptions()),
                SelectFilter::make('kode_akun')
                    ->label('Mata Anggaran')
                    ->options(fn (): array => static::getCoaTreeOptions()),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('cetak_pdf')
                        ->label('Cetak PDF')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn (Voucher $record): string => route('vouchers.pdf', $record))
                        ->openUrlInNewTab(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->requiresConfirmation()
                        ->authorizeIndividualRecords('delete')
                        ->action(fn(\Illuminate\Database\Eloquent\Collection $records) => $records->each->delete()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }

    public static function calculateTotalNominal(mixed $transactions): float
    {
        if (! is_iterable($transactions)) {
            return 0.0;
        }

        return (float) collect($transactions)->sum(function (mixed $transaction): float {
            if (! is_array($transaction)) {
                return 0.0;
            }

            $nominal = $transaction['nominal'] ?? 0;

            if (is_string($nominal)) {
                $nominal = str_replace([' ', ','], ['', ''], $nominal);
            }

            return is_numeric($nominal) ? (float) $nominal : 0.0;
        });
    }

    public static function formatCoaLabel(ChartOfAccount $coa): string
    {
        $depth = substr_count($coa->kode_akun, '.');
        $indent = str_repeat("\u{00A0}\u{00A0}\u{00A0}\u{00A0}", $depth);
        $icon = ! $coa->is_postable ? '📂' : '↳';

        return "{$indent}{$icon} {$coa->kode_akun} - {$coa->nama_akun}";
    }

    public static function getCoaTreeOptions(): array
    {
        return ChartOfAccount::orderBy('kode_akun')
            ->get()
            ->mapWithKeys(function (ChartOfAccount $coa) {
                return [$coa->kode_akun => static::formatCoaLabel($coa)];
            })
            ->toArray();
    }

    /**
     * Get options for the Cash or Bank account field, dynamically filtered by voucher type.
     */
    public static function getKasBankOptions(?string $jenisVoucher = null, ?string $currentCode = null): array
    {
        $query = ChartOfAccount::where('is_postable', true)
            ->where('kategori', 'Kas & Bank')
            ->orderBy('kode_akun');

        // BKK & BKM: Kas accounts
        if (in_array($jenisVoucher, ['BKK', 'BKM'], true)) {
            $kasAccounts = (clone $query)->where(function ($q) {
                $q->where('kode_akun', 'like', '111%')
                  ->orWhere('nama_akun', 'like', '%Kas%');
            })->get();

            if ($kasAccounts->isNotEmpty()) {
                $options = $kasAccounts->mapWithKeys(fn (ChartOfAccount $coa) => [$coa->kode_akun => "{$coa->kode_akun} - {$coa->nama_akun}"])->toArray();
                if ($currentCode && ! isset($options[$currentCode])) {
                    if ($curr = ChartOfAccount::find($currentCode)) {
                        $options[$currentCode] = "{$curr->kode_akun} - {$curr->nama_akun}";
                    }
                }
                return $options;
            }
        }

        // BBK & BBM: Bank accounts
        if (in_array($jenisVoucher, ['BBK', 'BBM'], true)) {
            $bankAccounts = (clone $query)->where(function ($q) {
                $q->where('kode_akun', 'not like', '111%')
                  ->where('nama_akun', 'not like', '%Kas Besar%')
                  ->where('nama_akun', 'not like', '%Kas Kecil%');
            })->get();

            if ($bankAccounts->isNotEmpty()) {
                $options = $bankAccounts->mapWithKeys(fn (ChartOfAccount $coa) => [$coa->kode_akun => "{$coa->kode_akun} - {$coa->nama_akun}"])->toArray();
                if ($currentCode && ! isset($options[$currentCode])) {
                    if ($curr = ChartOfAccount::find($currentCode)) {
                        $options[$currentCode] = "{$curr->kode_akun} - {$curr->nama_akun}";
                    }
                }
                return $options;
            }
        }

        // Default: All postable Kas & Bank
        $options = $query->get()
            ->mapWithKeys(fn (ChartOfAccount $coa) => [$coa->kode_akun => "{$coa->kode_akun} - {$coa->nama_akun}"])
            ->toArray();

        if ($currentCode && ! isset($options[$currentCode])) {
            if ($curr = ChartOfAccount::find($currentCode)) {
                $options[$currentCode] = "{$curr->kode_akun} - {$curr->nama_akun}";
            }
        }

        return $options;
    }

    /**
     * Get tree options for the Budget Account field, filtered by voucher type.
     */
    public static function getMataAnggaranTreeOptions(?string $jenisVoucher = null, ?string $currentCode = null): array
    {
        $allowedCategories = match ($jenisVoucher) {
            'BKK', 'Keluar', 'Bukti Kas Keluar (BKK)' => ['Pengeluaran', 'Kas & Bank', 'Hutang / Piutang', 'Hutang/Piutang', 'Hutang', 'Piutang'],
            'BKM', 'Masuk', 'Bukti Kas Masuk (BKM)' => ['Penerimaan', 'Kas & Bank', 'Hutang / Piutang', 'Hutang/Piutang', 'Hutang', 'Piutang'],
            'BBK', 'BBM', 'Bukti Bank Keluar (BBK)', 'Bukti Bank Masuk (BBM)' => null, // Bebas pilih semua mata anggaran
            default => null,
        };

        $query = ChartOfAccount::orderBy('kode_akun');
        if ($allowedCategories !== null) {
            $query->where(function ($q) use ($allowedCategories) {
                $q->whereIn('kategori', $allowedCategories)
                  ->orWhereNull('kategori');
            });
        }

        $options = $query->get()
            ->mapWithKeys(function (ChartOfAccount $coa) {
                return [$coa->kode_akun => static::formatCoaLabel($coa)];
            })
            ->toArray();

        if ($currentCode && ! isset($options[$currentCode])) {
            if ($curr = ChartOfAccount::find($currentCode)) {
                $options[$currentCode] = static::formatCoaLabel($curr);
            }
        }

        return $options;
    }

    /**
     * Konversi angka bulan (1-12) ke angka Romawi (I - XII).
     */
    public static function getRomawiMonth(int $month): string
    {
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $romawi[$month] ?? 'I';
    }

    /**
     * Generate Nomor Bukti otomatis dengan format:
     * [JENIS][3 DIGIT NO URUT]-[2 DIGIT MINGGU]-[BULAN ROMAWI]-[4 DIGIT TAHUN]
     * Contoh: BKK001-01-IV-2026
     */
    public static function generateNoBukti(?string $jenisVoucher, ?string $tanggal): string
    {
        $jenis = match ($jenisVoucher) {
            'BKK', 'Keluar', 'Bukti Kas Keluar (BKK)' => 'BKK',
            'BKM', 'Masuk', 'Bukti Kas Masuk (BKM)' => 'BKM',
            'BBK', 'Bukti Bank Keluar (BBK)' => 'BBK',
            'BBM', 'Bukti Bank Masuk (BBM)' => 'BBM',
            default => $jenisVoucher ?: 'BKM',
        };

        $date = $tanggal ? Carbon::parse($tanggal) : Carbon::now();
        $weekOfMonth = sprintf('%02d', (int) ceil($date->day / 7));
        $romawiMonth = static::getRomawiMonth((int) $date->month);
        $year = $date->year;

        $suffix = "{$weekOfMonth}-{$romawiMonth}-{$year}";
        $prefix = $jenis;

        // Ambil semua nomor bukti voucher sejenis dalam minggu dan bulan romawi yang sama
        $existingVouchers = Voucher::where(function ($q) use ($jenis) {
                $q->where('jenis_voucher', $jenis)
                  ->orWhere('no_bukti', 'like', "{$jenis}%");
            })
            ->where('no_bukti', 'like', "{$prefix}%-{$suffix}")
            ->pluck('no_bukti');

        $maxSeq = 0;
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)-' . preg_quote($suffix, '/') . '$/';

        foreach ($existingVouchers as $no) {
            if (preg_match($pattern, $no, $matches)) {
                $seq = (int) $matches[1];
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        $nextSeq = $maxSeq + 1;

        return sprintf('%s%03d-%s', $prefix, $nextSeq, $suffix);
    }
}

