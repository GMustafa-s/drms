<?php
namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class WellUsage extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Recently Added Well Usages';
    public function table(Table $table): Table
    {
        // Get the current tenant
        $tenant = Filament::getTenant();

        return $table
            ->query(\App\Models\WellUsage::where('company_id', $tenant->id))
            ->columns([
                Tables\Columns\TextColumn::make('well.lease')
                    ->label('Well Lease')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('product_type')
                    ->label('Product Type')
                    ->colors([
                        'primary' => 'Chemical',
                        'success' => 'Organic',
                        'danger' => 'Hazardous',
                    ])
                    ->searchable(),

                Tables\Columns\TextColumn::make('injection_location')
                    ->label('Injection Location')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ppm')
                    ->label('PPM')
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->searchable(),

                Tables\Columns\TextColumn::make('quarts_per_day')
                    ->label('Quarts/Day')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->searchable(),

                Tables\Columns\TextColumn::make('gallons_per_day')
                    ->label('Gallons/Day')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->searchable(),

                Tables\Columns\TextColumn::make('gallons_per_month')
                    ->label('Gallons/Month')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->searchable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('program')
                    ->label('Program')
                    ->searchable(),

                Tables\Columns\TextColumn::make('delivery_per_gallon')
                    ->label('Delivery/Gallon')
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 2))
                    ->searchable(),

                Tables\Columns\TextColumn::make('ppg')
                    ->label('PPG')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->searchable(),

                Tables\Columns\TextColumn::make('monthly_cost')
                    ->label('Monthly Cost')
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 2))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('bwe')
                    ->label('BWE')
                    ->searchable(),

                Tables\Columns\TextColumn::make('bowg')
                    ->label('BOWG')
                    ->searchable(),

                Tables\Columns\TextColumn::make('production_location')
                    ->label('Production Location')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('bopd')
                    ->label('BOPD')
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->searchable(),

//                Tables\Columns\ToggleColumn::make('is_published')
//                    ->label('Published')
//                    ->toggleable()
//                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Add a filter for published status
                Tables\Filters\SelectFilter::make('is_published')
                    ->label('Published Status')
                    ->options([
                        '1' => 'Published',
                        '0' => 'Unpublished',
                    ]),
//
//                // Add a date filter for the creation date
//                Tables\Filters\DateFilter::make('created_at')
//                    ->label('Creation Date'),

                // Add a filter for product type
                Tables\Filters\SelectFilter::make('product_type')
                    ->label('Product Type')
                    ->options([
                        'Chemical' => 'Chemical',
                        'Organic' => 'Organic',
                        'Hazardous' => 'Hazardous',
                    ]),
            ])
            ->actions([
//                // Add an edit action button
//                Tables\Actions\EditAction::make()
//                    ->label('Edit'),
//
//                // Add a delete action button
//                Tables\Actions\DeleteAction::make()
//                    ->label('Delete'),
            ])
            ->bulkActions([
                // Add a bulk delete action
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
