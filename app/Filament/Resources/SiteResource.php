<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages;
use App\Filament\Resources\SiteResource\RelationManagers;
use App\Filament\Resources\SiteResource\RelationManagers\WellsRelationManager;
use App\Livewire\BlogPostsChart;
use App\Models\Area;
use App\Models\Site;
use Carbon\Carbon;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class SiteResource extends Resource
{
    use InteractsWithPageFilters;
    protected static ?string $model = Site::class;
    protected static ?string $recordTitleAttribute = 'location';
    protected static ?string $navigationGroup = "Location Management";
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';


    public static function getWidgets(): array
    {
        return [
            BlogPostsChart::class
        ];
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('location')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Site name'),
                                Forms\Components\MarkdownEditor::make('comments')
//                            ->required()
                                    ->maxLength(255)
                                    ->placeholder('comments here'),

                            ]),
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Select::make('area_id')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->relationship('area', 'name', fn($query) => $query->where('company_id', Filament::getTenant()->id))
                                    ->getSearchResultsUsing(fn($query) => Area::where('company_id', Filament::getTenant()->id)
                                        ->where('name', 'like', "%{$query}%")
                                        ->pluck('name', 'id')
                                        ->toArray())
                                    ->getOptionLabelUsing(fn($value) => Area::where('company_id', Filament::getTenant()->id)
                                        ->find($value)?->name),
                                Forms\Components\Toggle::make('is_published')
                                    ->required()
                                    ->label('Status')
                                    ->default('published')

                            ])
                    ]),
            ]);
    }


    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        // Get the current tenant
        $tenant = Filament::getTenant();
        return $table
            ->columns([
                Stack::make([
                    // Columns
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->headerActions([
                ExportAction::make(),

                // ...
            ]) ->
           columns([
                    Tables\Columns\TextColumn::make('location')
                        ->searchable(),
                TextColumn::make('monthly_cost_by_site')
                    ->label('Monthly Cost by Site')
                    ->formatStateUsing(function ($record) {
                        // Get the selected month from the filter
                        $reportMonth = request()->input('tableFilters.month_selector.report_month', now()->format('Y-m'));
                        return $record->monthlyCost($record->id, $reportMonth);
                    })
                    ->default('0'),

                TextColumn::make('BWE_by_site')
                    ->label('BWE by Site')
                    ->formatStateUsing(function ($record) {
                        // Get the selected month from the filter
                        $reportMonth = request()->input('tableFilters.month_selector.report_month', now()->format('Y-m'));
                        return $record->BWE($record->id, $reportMonth);
                    })
                    ->default('0'),

                TextColumn::make('BWPD_by_site')
                    ->label('BWPD by Site')
                    ->formatStateUsing(function ($record) {
                        // Get the selected month from the filter
                        $reportMonth = request()->input('tableFilters.month_selector.report_month', now()->format('Y-m'));
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
                Filter::make('is_published')
                    ->toggle()
                    ->label('Status')
                    ->query(fn(Builder $query): Builder => $query->where('is_published', true)),
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
                    ->default(now()->format('Y-m'))
                    ->form([
                        Flatpickr::make('report_month')
                            ->label('Filter Each Site Data by Month')
                            ->monthSelect()
                            ->animate()
                    ])

            ])
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->selectable()
            ->actions([
//                Action::make('duplicate')
//                    ->label('Duplicate')
//                    ->icon('heroicon-o-document-duplicate') // Use an appropriate icon here
//                    ->action(function ($record) {
//                        // Duplicate the record
//                        $duplicate = $record->replicate(); // Clone the record
//                        $duplicate->save(); // Save the new cloned record
//
//                        // Use Filament's resource URL helper for redirection
//                        return redirect(SiteResource::getUrl('edit', ['record' => $duplicate->id]));
//                    }),
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
            WellsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
            'view' => Pages\ViewSite::route('/{record}'),

        ];
    }
    public function updatedFilter($filter)
    {
        // Emit event to refresh the table
        $this->emit('refreshTable');
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Details')->schema([
                    Grid::make(2)->schema([


                        TextEntry::make('location'),
                        TextEntry::make('area.name')
                            ->badge('true')
                            ->color('info'),

                        TextEntry::make('comments')
                            ->columnSpanFull()

                    ])
                ]),
            ]);
    }



    public function monthlyCost($id): float
    {
//        $id = 1;
//        dd($this->filters['report_month']);
        // Get the selected month from the filter form in the dashboard
        $selectedMonth = $this->filters['report_month'] ?? now()->format('Y-m');

        // Parse the selected month to determine the start and end date
        $startDate = Carbon::parse($selectedMonth)->startOfMonth();
        $endDate = Carbon::parse($selectedMonth)->endOfMonth();

        // Get the current tenant
        $tenant = Filament::getTenant();

        // Query all sites for the current tenant
        $sites = Site::where('company_id', $tenant->id)->where('id', $id)->get();

        // Prepare data for the chart
        $total =  $sites->map(function (Site $site) use ($startDate, $endDate) {
            // Calculate the total monthly cost for the site
            return $site->wells()
                ->with(['wellUsages' => function ($query) use ($startDate, $endDate) {
                    // Only consider WellUsages within the selected month range
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }])
                ->get()
                ->flatMap(fn($well) => $well->wellUsages)
                ->sum('monthly_cost'); // Sum up the monthly cost from all WellUsages for the sit
        });

        if(is_array($total->all()) && isset($total->all()[0]))  return $total->all()[0];
        return 0;
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
