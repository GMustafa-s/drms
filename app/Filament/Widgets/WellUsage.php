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
                    ->label('Monthly Cost by Site')
                    ->formatStateUsing(function ($record) {
                        $reportMonth = null;
                        if (request()->has('components') && isset(request()->get('components')[0]['updates']['tableFilters.month_selector.report_month'])) {
                            $reportMonth = request()->get('components')[0]['updates']['tableFilters.month_selector.report_month'];
                        }else{
                            $reportMonth = request()->input('tableFilters.month_selector.report_month', now()->format('Y-m'));
                        }

                        return $record->monthlyCost($record->id, $reportMonth);
                    })
                    ->default('0'),

                TextColumn::make('BWE_by_site')
                    ->label('BWE by Site')
                    ->formatStateUsing(function ($record) {
                        $reportMonth = null;
                        if (request()->has('components') && isset(request()->get('components')[0]['updates']['tableFilters.month_selector.report_month'])) {
                            $reportMonth = request()->get('components')[0]['updates']['tableFilters.month_selector.report_month'];
                        }else{
                            $reportMonth = request()->input('tableFilters.month_selector.report_month', now()->format('Y-m'));
                        }

                        return $record->BWE($record->id, $reportMonth);
                    })
                    ->default('0'),

                TextColumn::make('BWPD_by_site')
                    ->label('BWPD by Site')
                    ->formatStateUsing(function ($record) {
                        $reportMonth = null;
                        if (request()->has('components') && isset(request()->get('components')[0]['updates']['tableFilters.month_selector.report_month'])) {
                            $reportMonth = request()->get('components')[0]['updates']['tableFilters.month_selector.report_month'];
                        }else{
                            $reportMonth = request()->input('tableFilters.month_selector.report_month', now()->format('Y-m'));
                        }
                        return $record->bwpd($record->id, $reportMonth);
                    })
                    ->default('0'),
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
