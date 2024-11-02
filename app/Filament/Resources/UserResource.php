<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'System Management';
    protected static ?int $navigationSort = 8;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static bool $isScopedToTenant = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(),

                // Hidden field to auto-assign the current user's company
                Forms\Components\Select::make('companies')
                    ->label('Assign Companies')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->relationship('companies', 'name')
                    ->options(Company::all()->pluck('name', 'id'))
                    ->required(),
                // Add this MultiSelect field for Role Assignment
                Forms\Components\MultiSelect::make('roles')
                    ->label('Assign Roles')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->relationship('roles', 'name') // Use relationship method
                    ->options(Role::all()->pluck('name', 'id')) // Display available roles
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),

                // Display assigned roles
                Tables\Columns\BadgeColumn::make('roles.name')
                    ->label('Roles'),
                // Display assigned roles
                TagsColumn::make('companies')
                    ->label('Assigned Companies')
                    ->getStateUsing(function ($record) {
                        // Assuming $record->companies gives you the related companies
                        return $record->companies->pluck('name')->toArray();
                    })
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }


    public static function can($action, $record = null): bool
    {
        $user = auth()->user();

        // Super Admin has full access
        if ($user->hasRole('Super Admin')) {
            return true;
        }


        // Other users do not have any access
        return false;
    }
}
