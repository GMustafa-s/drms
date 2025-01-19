<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OilAnalysisResource\Pages;
use App\Models\OilAnalysis;
use App\Models\Well;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OilAnalysisResource extends Resource
{
    protected static ?string $model = OilAnalysis::class;
// In OilAnalysisResource (App\Filament\Resources\OilAnalysisResource)
//    public static ?string $tenantRelationshipName = 'OilAnalyses';

    protected static ?string $label = 'Oil Analysis';
    protected static ?string $navigationGroup = "Well Management";
    protected static ?string $navigationParentItem ='Wells';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                Forms\Components\Select::make('well_id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('well', 'lease', fn($query) => $query->where('company_id', Filament::getTenant()->id))
                    ->getSearchResultsUsing(fn($query) => Well::where('company_id', Filament::getTenant()->id)
                        ->where('name', 'like', "%{$query}%")
                        ->pluck('name', 'id')
                        ->toArray())
                    ->getOptionLabelUsing(fn($value) => Well::where('company_id', Filament::getTenant()->id)
                        ->find($value)?->name)
                    ->reactive()
                    ->searchable(),
                Forms\Components\TextInput::make('sample_point')

                    ->required(),
                Forms\Components\DatePicker::make('sample_date')
                    ->native(false)
                    ->required(),
                Forms\Components\TextInput::make('api_gravity')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('pour_ptf')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('cloud_ptf')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('praffin')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('asphaltene')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('comments')
//                    ->required(),
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('well.lease')
                    ->sortable()
                    ->label('Well Name'),
                Tables\Columns\TextColumn::make('sample_point')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sample_date')
//                    ->native(false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('api_gravity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pour_ptf')
//                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cloud_ptf')
                    ->sortable(),
                Tables\Columns\TextColumn::make('praffin')
                    ->sortable(),
                Tables\Columns\TextColumn::make('asphaltene')
                    ->sortable(),
                Tables\Columns\TextColumn::make('comments'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('well_id')
                    ->label('Select Well')
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        $tenant = Filament::getTenant();
                        return \App\Models\Well::where('company_id', $tenant->id)
                            ->pluck('lease', 'id')
                            ->toArray();
                    })
            ], layout: FiltersLayout::AboveContent)
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
            'index' => Pages\ListOilAnalyses::route('/'),
            'create' => Pages\CreateOilAnalysis::route('/create'),
            'edit' => Pages\EditOilAnalysis::route('/{record}/edit'),
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
