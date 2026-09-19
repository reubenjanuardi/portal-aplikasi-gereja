<?php

namespace App\Filament\Settings\Resources;

use App\Filament\Settings\Resources\UserResource\Pages;
use App\Models\ActivityLog;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use BackedEnum;
use UnitEnum;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static UnitEnum|string|null $navigationGroup = 'Manajemen Akses';

    protected static ?string $navigationLabel = 'Pengguna Portal';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Daftar Pengguna';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Akun')
                ->description('Data login dan identitas pengguna portal.')
                ->icon('heroicon-o-user-circle')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Alamat Email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    TextInput::make('password')
                        ->label('Kata Sandi (Password)')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => ! empty($state) ? Hash::make($state) : null)
                        ->dehydrated(fn ($state) => ! empty($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->maxLength(255)
                        ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password.' : 'Minimal 8 karakter.'),

                    Toggle::make('is_active')
                        ->label('Akun Aktif')
                        ->default(true)
                        ->helperText('Jika dinonaktifkan, pengguna tidak akan bisa login ke seluruh sistem portal.')
                        ->required(),
                ])
                ->columns(2),

            Section::make('Penetapan Peran (Roles)')
                ->description('Pilih satu atau lebih peran untuk pengguna ini.')
                ->icon('heroicon-o-shield-check')
                ->schema([
                    CheckboxList::make('roles')
                        ->label('Pilihan Peran')
                        ->relationship('roles', 'name')
                        ->getOptionLabelFromRecordUsing(fn (Role $record): string => ($record->display_name ?? $record->name) . ' — ' . ($record->description ?? ''))
                        ->columns(1)
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.display_name')
                    ->label('Peran (Role)')
                    ->badge()
                    ->color('primary')
                    ->default(fn (User $record) => $record->roles->pluck('name')->join(', ')),

                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('Belum pernah login'),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('reset_password')
                        ->label('Reset Password')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->modalHeading(fn (User $record): string => "Reset Password — {$record->name}")
                        ->modalWidth('lg')
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->modalContent(fn (User $record) => view('filament.settings.modals.reset-password-flow', [
                            'record' => $record,
                            'expireMinutes' => (int) config('auth.passwords.users.expire', 60),
                        ]))
                        ->authorize(fn (User $record): bool => auth()->user()?->can('portal.user.manage') && $record->is_active)
                        ->action(function (User $record) {
                            $token = Password::broker()->createToken($record);
                            $admin = auth()->user();
                            $adminName = $admin?->name ?? 'Administrator';

                            ActivityLog::log(
                                description: "Administrator [{$adminName}] membuat link reset password untuk pengguna [{$record->name}]",
                                logName: 'user_management',
                                subject: $record,
                                properties: [
                                    'target_user_id' => $record->id,
                                    'target_user_email' => $record->email,
                                ]
                            );
                        }),
                    DeleteAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
