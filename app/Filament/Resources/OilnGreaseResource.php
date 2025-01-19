<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OilnGreaseResource\Pages;
use App\Filament\Resources\OilnGreaseResource\RelationManagers;
use App\Models\OilAnalysis;
use App\Models\OilnGrease;
use App\Models\Site;
use App\Models\Well;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OilnGreaseResource extends Resource
{
    protected static ?string $model = OilnGrease::class;
// In OilAnalysisResource (App\Filament\Resources\OilAnalysisResource)
//    public static ?string $tenantRelationshipName = 'OilAnalyses';

    protected static ?string $label = 'Oil & Grease ';
    protected static ?string $navigationGroup = "Location Management";
    protected static ?string $navigationParentItem ='Sites';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('site_id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('site', 'location', fn($query) => $query->where('company_id', Filament::getTenant()->id))
                    ->getSearchResultsUsing(fn($query) => Site::where('company_id', Filament::getTenant()->id)
                        ->where('name', 'like', "%{$query}%")
                        ->pluck('name', 'id')
                        ->toArray())
                    ->getOptionLabelUsing(fn($value) => Site::where('company_id', Filament::getTenant()->id)
                        ->find($value)?->name)
                    ->searchable(),
                Forms\Components\TextInput::make('sample_point')
                ->required(),
                Forms\Components\TextInput::make('sample_date')
                ->required(),
                Forms\Components\TextInput::make('o_g_ppm')
                    ->label('O & G PPM')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('comments'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
//                Tables\Columns\TextColumn::make('company.name')
//                    ->numeric()
//                    ->sortable(),
                Tables\Columns\TextColumn::make('site.location')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sample_point')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sample_date')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('o_g_ppm')
                    ->label('O & G PPM')
                    ->searchable()
                    ->colors([
                        'danger' => fn ($state): bool => $state > 100, // Red color for values greater than 100
                        'success' => fn ($state): bool => $state <= 100, // Green color for values 100 or less
                    ]),

                Tables\Columns\TextColumn::make('comments')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListOilnGreases::route('/'),
            'create' => Pages\CreateOilnGrease::route('/create'),
            'edit' => Pages\EditOilnGrease::route('/{record}/edit'),
        ];
    }

    public static function can($action, $record = null): bool
    {
        $user = auth()->user();

        // Super Admin has full access
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Panel User has restricted access (view & viewAny only)
        if ($user->hasRole('Panel User')) {
            if (in_array($action, ['view', 'viewAny'])) {
                return true;
            } else {
                return false;
            }
        }

        // Other users do not have any access
        return false;
    }
}
