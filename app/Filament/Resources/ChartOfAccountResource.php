<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChartOfAccountResource\Pages;
use App\Models\ChartOfAccount;
use App\Support\Format;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class ChartOfAccountResource extends Resource
{
    protected static ?string $model = ChartOfAccount::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-circle-stack';

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $recordTitleAttribute = 'nama_akun';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['children', 'parent']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('kode_akun')
                ->label('Kode Akun')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->rule('regex:/^[^,]+$/')
                ->disabled(fn(string $operation): bool => $operation === 'edit')
                ->dehydrated(fn(string $operation): bool => $operation === 'create'),

            TextInput::make('nama_akun')
                ->label('Nama Akun')
                ->required()
                ->maxLength(255),

            TextInput::make('budget')
                ->label('Budget (Anggaran PKA)')
                ->numeric()
                ->prefix('Rp')
                ->default(0)
                ->minValue(0)
                ->helperText('Patokan anggaran statis tahunan untuk akun ini.'),

            Select::make('kategori')
                ->required()
                ->options([
                    'Penerimaan' => 'Penerimaan',
                    'Pengeluaran' => 'Pengeluaran',
                    'Kas & Bank' => 'Kas & Bank',
                    'Hutang / Piutang' => 'Hutang / Piutang',
                ]),

            Select::make('parent_code')
                ->label('Parent Code')
                ->nullable()
                ->searchable()
                ->placeholder('Tidak memiliki parent')
                ->options(fn($record): array => static::getParentAccountOptions($record?->kode_akun ?? null)),

            Toggle::make('is_postable')
                ->label('Is Postable')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('kode_akun')
            ->columns([
                TextColumn::make('kode_akun')
                    ->label('Kode Akun')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_akun')
                    ->label('Nama Akun')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function (string $state, ChartOfAccount $record): string {
                        return $record->parent_code ? '↳ ' . $state : $state;
                    }),

                TextColumn::make('budget')
                    ->label('Budget (Anggaran)')
                ->formatStateUsing(fn ($state): string => Format::rupiah($state))
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('kategori')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state): string => match ($state) {
                        'Penerimaan' => 'success',
                        'Pengeluaran' => 'danger',
                        'Kas & Bank' => 'info',
                        'Hutang / Piutang' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('is_postable')
                    ->label('Postable')
                    ->boolean()
                    ->sortable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function (ChartOfAccount $record, DeleteAction $action): void {
                            // Cek apakah akun masih punya anak (child accounts)
                            if (ChartOfAccount::where('parent_code', $record->kode_akun)->exists()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Akun Tidak Dapat Dihapus')
                                    ->body('Akun "' . $record->nama_akun . '" masih memiliki akun anak. Hapus atau pindahkan akun anak terlebih dahulu.')
                                    ->send();
                                $action->cancel();
                                return;
                            }

                            // Cek apakah akun sudah punya riwayat transaksi
                            if ($record->transactions()->exists()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Akun Tidak Dapat Dihapus')
                                    ->body('Akun "' . $record->nama_akun . '" sudah memiliki riwayat transaksi dan tidak dapat dihapus.')
                                    ->send();
                                $action->cancel();
                            }
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->requiresConfirmation()
                        ->authorizeIndividualRecords('delete')
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records, BulkAction $action): void {
                            $blocked = $records->filter(function (ChartOfAccount $record): bool {
                                return ChartOfAccount::where('parent_code', $record->kode_akun)->exists()
                                    || $record->transactions()->exists();
                            });

                            if ($blocked->isNotEmpty()) {
                                $names = $blocked->pluck('nama_akun')->join(', ');
                                Notification::make()
                                    ->danger()
                                    ->title('Beberapa Akun Tidak Dapat Dihapus')
                                    ->body('Akun berikut masih memiliki anak atau riwayat transaksi: ' . $names)
                                    ->send();
                                $action->cancel();
                            }
                        })
                        ->action(fn(\Illuminate\Database\Eloquent\Collection $records) => $records->each->delete()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChartOfAccounts::route('/'),
            'create' => Pages\CreateChartOfAccount::route('/create'),
            'edit' => Pages\EditChartOfAccount::route('/{record}/edit'),
        ];
    }

    public static function getParentAccountOptions(?string $exceptKodeAkun = null): array
    {
        $accounts = ChartOfAccount::query()
            ->when($exceptKodeAkun, fn($query) => $query->where('kode_akun', '!=', $exceptKodeAkun))
            ->orderBy('kode_akun')
            ->get(['kode_akun', 'nama_akun', 'parent_code']);

        $grouped = $accounts->groupBy('parent_code');
        $options = [];
        $visited = [];

        $walk = function (?string $parentCode = null, int $depth = 0) use (&$walk, $grouped, &$options, &$visited): void {
            foreach ($grouped->get($parentCode, collect()) as $account) {
                $visited[$account->kode_akun] = true;
                $options[$account->kode_akun] = str_repeat('— ', $depth) . $account->kode_akun . ' - ' . $account->nama_akun;
                $walk($account->kode_akun, $depth + 1);
            }
        };

        $walk();

        foreach ($accounts as $account) {
            if (! isset($visited[$account->kode_akun])) {
                $options[$account->kode_akun] = $account->kode_akun . ' - ' . $account->nama_akun;
            }
        }

        return $options;
    }
}
