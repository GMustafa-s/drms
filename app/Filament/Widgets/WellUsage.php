<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class WellUsage extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\WellUsage::query())
            ->columns([
                Tables\Columns\TextColumn::make('well.lease')
                    ->searchable()
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
                Tables\Columns\ToggleColumn::make('is_published')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}
