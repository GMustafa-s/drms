<?php

namespace App\Filament\Widgets;

use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class WellUsage extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Sites Overview';
    //    public function table(Table $table): Table
    //    {
    //        // Get the current tenant
    //        $tenant = Filament::getTenant();
    //
    //        return $table
    //            ->query(\App\Models\WellUsage::where('company_id', $tenant->id))
    //            ->columns([
    //                Tables\Columns\TextColumn::make('well.lease')
    //                    ->label('Well Lease')
    //                    ->searchable()
    //                    ->sortable(),
    //
    //                Tables\Columns\TextColumn::make('product_name')
    //                    ->label('Product Name')
    //                    ->searchable()
    //                    ->sortable(),
    //
    //                Tables\Columns\BadgeColumn::make('product_type')
    //                    ->label('Product Type')
    //                    ->colors([
    //                        'primary' => 'Chemical',
    //                        'success' => 'Organic',
    //                        'danger' => 'Hazardous',
    //                    ])
    //                    ->searchable(),
    //
    //                Tables\Columns\TextColumn::make('injection_location')
    //                    ->label('Injection Location')
    //                    ->searchable()
    //                    ->sortable(),
    //
    //                Tables\Columns\TextColumn::make('ppm')
    //                    ->label('PPM')
    //                    ->formatStateUsing(fn($state) => number_format($state))
    //                    ->searchable(),
    //
    //                Tables\Columns\TextColumn::make('quarts_per_day')
    //                    ->label('Quarts/Day')
    //                    ->formatStateUsing(fn($state) => number_format($state, 2))
    //                    ->searchable(),
    //
    //                Tables\Columns\TextColumn::make('gallons_per_day')
    //                    ->label('Gallons/Day')
    //                    ->formatStateUsing(fn($state) => number_format($state, 2))
    //                    ->searchable(),
    //
    //                Tables\Columns\TextColumn::make('gallons_per_month')
    //                    ->label('Gallons/Month')
    //                    ->formatStateUsing(fn($state) => number_format($state, 2))
    //                    ->searchable(),
    //
    //                // Tables\Columns\TextColumn::make('location')
    //                //     ->label('Location')
    //                //     ->searchable()
    //                //     ->sortable(),
    //
    //                // Tables\Columns\TextColumn::make('program')
    //                //     ->label('Program')
    //                //     ->searchable(),
    //
    //                Tables\Columns\TextColumn::make('delivery_per_gallon')
    //                    ->label('Delivery/Gallon')
    //                    ->formatStateUsing(fn($state) => '$' . number_format($state, 2))
    //                    ->searchable(),
    //
    //                Tables\Columns\TextColumn::make('ppg')
    //                    ->label('PPG')
    //                    ->formatStateUsing(fn($state) => number_format($state, 2))
    //                    ->searchable(),
    //
    //                Tables\Columns\TextColumn::make('monthly_cost')
    //                    ->label('Monthly Cost')
    //                    ->formatStateUsing(fn($state) => '$' . number_format($state, 2))
    //                    ->sortable()
    //                    ->searchable(),
    //
    //                Tables\Columns\TextColumn::make('bwe')
    //                    ->label('BWE')
    //                    ->searchable(),
    //
    //                Tables\Columns\TextColumn::make('bowg')
    //                    ->label('BOWG')
    //                    ->searchable(),
    //
    //                // Tables\Columns\TextColumn::make('production_location')
    //                //     ->label('Production Location')
    //                //     ->searchable()
    //                //     ->sortable(),
    //
    //                Tables\Columns\TextColumn::make('bopd')
    //                    ->label('BOPD')
    //                    ->formatStateUsing(fn($state) => number_format($state))
    //                    ->searchable(),
    //
    //                //                Tables\Columns\ToggleColumn::make('is_published')
    //                //                    ->label('Published')
    //                //                    ->toggleable()
    //                //                    ->sortable(),
    //
    //                Tables\Columns\TextColumn::make('created_at')
    //                    ->label('Created At')
    //                    ->dateTime('M d, Y H:i')
    //                    ->sortable()
    //                    ->toggleable(isToggledHiddenByDefault: true),
    //
    //                Tables\Columns\TextColumn::make('updated_at')
    //                    ->label('Updated At')
    //                    ->dateTime('M d, Y H:i')
    //                    ->sortable()
    //                    ->toggleable(isToggledHiddenByDefault: true),
    //            ])
    //            ->filters([
    //                // Add a filter for published status
    //                Tables\Filters\SelectFilter::make('is_published')
    //                    ->label('Published Status')
    //                    ->options([
    //                        '1' => 'Published',
    //                        '0' => 'Unpublished',
    //                    ]),
    //                //
    //                //                // Add a date filter for the creation date
    //                //                Tables\Filters\DateFilter::make('created_at')
    //                //                    ->label('Creation Date'),
    //
    //                // Add a filter for product type
    //                Tables\Filters\SelectFilter::make('product_type')
    //                    ->label('Product Type')
    //                    ->options([
    //                        'Chemical' => 'Chemical',
    //                        'Organic' => 'Organic',
    //                        'Hazardous' => 'Hazardous',
    //                    ]),
    //            ])
    //            ->actions([
    //                //                // Add an edit action button
    //                //                Tables\Actions\EditAction::make()
    //                //                    ->label('Edit'),
    //                //
    //                //                // Add a delete action button
    //                //                Tables\Actions\DeleteAction::make()
    //                //                    ->label('Delete'),
    //            ])
    //            ->bulkActions([
    //                // Add a bulk delete action
    //                Tables\Actions\DeleteBulkAction::make(),
    //            ]);
    //    }

    /**
     * @throws \Exception
     */
    public function table(Table $table): Table
    { // Get the current tenant
        $tenant = Filament::getTenant();
        return $table
            ->query(\App\Models\Site::where('company_id', $tenant->id))
            ->columns([
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                TextColumn::make('monthly_cost_by_site')
                    ->formatStateUsing(function ($record) {
                        // Assuming 'report_month' is the name of your filter
                        $reportMonth = request()->input('tableFilters.month_selector.report_month'); // Provide a default value if needed
                        return $record->monthlyCost($record->id, $reportMonth);
                    })
                    ->default('555'),
                TextColumn::make('BWE by site')
                    ->label('BWE by site')
                    ->formatStateUsing(function ($record) {
                        // Assuming 'report_month' is the name of your filter
                        $reportMonth = request()->input('tableFilters.month_selector.report_month'); // Provide a default value if needed
                        return $record->BWE($record->id, $reportMonth);
                    })
                    ->default('555'),
                TextColumn::make('BWPD by site')
                    ->label('BWPD by Site')
                    ->formatStateUsing(function ($record) {
                        // Assuming 'report_month' is the name of your filter
                        $reportMonth = request()->input('tableFilters.month_selector.report_month'); // Provide a default value if needed
                        return $record->bwpd($record->id, $reportMonth);
                    })
                    ->default('555'),
                Tables\Columns\TextColumn::make('comments')
                    ->searchable()
                    ->label('Description'),
                Tables\Columns\TextColumn::make('area.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Status'),
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

                Tables\Filters\SelectFilter::make('area_id')
                    ->label('Related to Area')
                    ->searchable()
                    ->preload()
                    ->options(function () use ($tenant) {
                        // Fetch the areas related to the current tenant
                        return \App\Models\Area::where('company_id', $tenant->id)
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
                Filter::make('month_selector')
                    ->default(now())
                    ->form(
                        [
                            Flatpickr::make('report_month')->label('Filter Each Site Data by Month')->monthSelect()->animate()
                        ]
                    ),


            ])
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->selectable();
    }
}
