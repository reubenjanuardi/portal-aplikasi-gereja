<?php

namespace App\Filament\Settings\Resources;

use App\Filament\Settings\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static UnitEnum|string|null $navigationGroup = 'Sistem & Keamanan';

    protected static ?string $navigationLabel = 'Log Aktivitas Sistem';

    protected static ?string $modelLabel = 'Log Aktivitas';

    protected static ?string $pluralModelLabel = 'Riwayat Log Aktivitas';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Rincian Log Aktivitas')
                ->schema([
                    TextInput::make('created_at')
                        ->label('Waktu Kejadian (WIB)')
                        ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i:s') . ' WIB' : '-')
                        ->disabled(),

                    TextInput::make('causer.name')
                        ->label('Pengguna Pelaksana')
                        ->disabled(),

                    TextInput::make('log_name')
                        ->label('Kategori / Modul')
                        ->disabled(),

                    TextInput::make('subject_type')
                        ->label('Model Terkait')
                        ->formatStateUsing(fn ($state, ActivityLog $record) => $state ? class_basename($state) . " (#{$record->subject_id})" : '-')
                        ->disabled(),

                    Textarea::make('description')
                        ->label('Deskripsi Aksi')
                        ->rows(2)
                        ->columnSpanFull()
                        ->disabled(),

                    Placeholder::make('properties_formatted')
                        ->label('Data Perubahan (Payload / Diff)')
                        ->columnSpanFull()
                        ->content(function (ActivityLog $record): \Illuminate\Support\HtmlString {
                            $props = $record->properties ?? [];
                            $json = json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                            return new \Illuminate\Support\HtmlString(
                                "<pre class='p-4 bg-slate-900 text-emerald-400 rounded-xl text-xs overflow-x-auto font-mono max-h-96 leading-relaxed'>{$json}</pre>"
                            );
                        }),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu (WIB)')
                    ->dateTime('d M Y, H:i:s', 'Asia/Jakarta')
                    ->sortable()
                    ->width('180px'),

                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable()
                    ->default(fn (ActivityLog $record) => $record->causer?->name ?? 'Sistem / Anonim')
                    ->weight('semibold'),

                TextColumn::make('log_name')
                    ->label('Modul')
                    ->badge()
                    ->colors([
                        'warning' => 'keuangan',
                        'info' => 'portal_settings',
                        'success' => 'auth',
                        'gray' => 'default',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'keuangan' => 'Keuangan',
                        'portal_settings' => 'Pengaturan Portal',
                        'auth' => 'Autentikasi',
                        default => ucfirst($state),
                    }),

                TextColumn::make('description')
                    ->label('Deskripsi Aktivitas')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('properties.ip')
                    ->label('IP Address')
                    ->fontFamily('mono')
                    ->size('xs')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Kategori Modul')
                    ->options([
                        'keuangan' => 'Keuangan',
                        'portal_settings' => 'Pengaturan Portal',
                        'auth' => 'Autentikasi',
                    ]),

                SelectFilter::make('causer_id')
                    ->label('Pengguna')
                    ->options(fn () => User::pluck('name', 'id')->toArray()),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat Detail')
                    ->modalHeading('Rincian Log Aktivitas Sistem'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
