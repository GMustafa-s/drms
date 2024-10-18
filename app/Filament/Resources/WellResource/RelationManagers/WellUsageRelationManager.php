<?php

namespace App\Filament\Resources\WellResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class WellUsageRelationManager extends RelationManager
{
    protected static string $relationship = 'WellUsage';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_name')
                    ->datalist([
                        'BWM',
                        'Ford',
                        'Mercedes-Benz',
                        'Porsche',
                        'Toyota',
                        'Tesla',
                        'Volkswagen',
                    ])
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('product_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('injection_location')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ppm')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('quarts_per_day')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('gallons_per_day')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('gallons_per_month')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('location')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('program')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('delivery_per_gallon')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ppg')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('monthly_cost')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bwe')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bowg')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('production_location')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bopd')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_published')
                    ->required()
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ppm')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Month')
                    ->dateTime(' M, y ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('injection_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ppm')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quarts_per_day')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gallons_per_day')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gallons_per_month')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('program')
                    ->searchable(),
                Tables\Columns\TextColumn::make('delivery_per_gallon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ppg')
                    ->searchable(),
                Tables\Columns\TextColumn::make('monthly_cost')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bwe')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bowg')
                    ->searchable(),
                Tables\Columns\TextColumn::make('production_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bopd')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                ExportAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
