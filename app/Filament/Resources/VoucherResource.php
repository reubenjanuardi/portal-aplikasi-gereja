<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Models\ChartOfAccount;
use App\Models\Voucher;
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
                ->dehydrateStateUsing(fn (?string $state) => $state ? str_replace([',', ' '], ['', ''], $state) : $state)
                ->disabled(fn(string $operation): bool => $operation === 'edit')
                ->dehydrated(fn(string $operation): bool => $operation === 'create'),

            DatePicker::make('tanggal')
                ->required()
                ->default(fn(): string => now()->toDateString()),

            TextInput::make('pihak_terkait')
                ->label('Pihak Terkait')
                ->required()
                ->maxLength(255),

            Select::make('jenis_voucher')
                ->label('Jenis Voucher')
                ->required()
                ->options([
                    'BKM' => 'Bukti Kas Masuk (BKM)',
                    'BKK' => 'Bukti Kas Keluar (BKK)',
                    'BBM' => 'Bukti Bank Masuk (BBM)',
                    'BBK' => 'Bukti Bank Keluar (BBK)',
                ])
                ->live()
                ->afterStateUpdated(function (?string $state, $set, $get, string $operation): void {
                    if ($operation !== 'create' || ! $state) {
                        return;
                    }

                    $currentNo = (string) ($get('no_bukti') ?? '');
                    $prefixes = ['BKM', 'BKK', 'BBM', 'BBK'];
                    $matched = false;

                    foreach ($prefixes as $prefix) {
                        if (str_starts_with($currentNo, $prefix)) {
                            $suffix = substr($currentNo, strlen($prefix));
                            $set('no_bukti', $state . $suffix);
                            $matched = true;
                            break;
                        }
                    }

                    if (! $matched) {
                        $set('no_bukti', $state . $currentNo);
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
                ->optionsLimit(500)
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
                    'BKK', 'BBK' => 'Semua baris item dalam voucher ini akan dicatat pada pos mata anggaran pengeluaran yang sama.',
                    'BKM', 'BBM' => 'Semua baris item dalam voucher ini akan dicatat pada pos mata anggaran penerimaan yang sama.',
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
     * Get tree options for the Budget Account field, filtered by transaction direction.
     */
    public static function getMataAnggaranTreeOptions(?string $jenisVoucher = null, ?string $currentCode = null): array
    {
        $kategori = match ($jenisVoucher) {
            'BKK', 'BBK' => 'Pengeluaran',
            'BKM', 'BBM' => 'Penerimaan',
            default => null,
        };

        $query = ChartOfAccount::orderBy('kode_akun');
        if ($kategori) {
            $query->where(function ($q) use ($kategori) {
                $q->where('kategori', $kategori)
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
}
