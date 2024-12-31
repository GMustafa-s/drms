<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use App\Filament\Resources\AreaResource\RelationManagers;
use App\Models\Area;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;
    protected static ?string $navigationGroup = 'Location Management';
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $tenantOwnershipRelationshipName = 'company';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([

                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\MarkdownEditor::make('description')
//                                    ->required()
                                ,
                                Forms\Components\Toggle::make('is_published')
                                    ->required()
                                    ->default(true),
                            ])
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()   // ...
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->sortable()
                    ->badge(true),
                TextColumn::make('monthly_cost_by_area')
                    ->label('Monthly Cost by Area')
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
                TextColumn::make('Bowg_by_area')
                    ->label('BOWG by Area')
                    ->formatStateUsing(function ($record) {
                        $reportMonth = null;
                        if (request()->has('components') && isset(request()->get('components')[0]['updates']['tableFilters.month_selector.report_month'])) {
                            $reportMonth = request()->get('components')[0]['updates']['tableFilters.month_selector.report_month'];
                        }else{
                            $reportMonth = request()->input('tableFilters.month_selector.report_month', now()->format('Y-m'));
                        }

                        return $record->BOWG($record->id, $reportMonth);
                    })
                    ->default('0'),
                TextColumn::make('BWE by Area')
                    ->label('BWE by Area')
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
                // TextColumn::make('BWPD by Area')
                //     ->label('BWPD by Area')
                //     ->formatStateUsing(function ($record) {
                //         $reportMonth = null;
                //         if (request()->has('components') && isset(request()->get('components')[0]['updates']['tableFilters.month_selector.report_month'])) {
                //             $reportMonth = request()->get('components')[0]['updates']['tableFilters.month_selector.report_month'];
                //         }else{
                //             $reportMonth = request()->input('tableFilters.month_selector.report_month', now()->format('Y-m'));
                //         }

                //         return $record->bwpd($record->id, $reportMonth);
                //     })
                //     ->default('0'),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),
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
            ->selectable()
            ->actions([
                Tables\Actions\ViewAction::make(),
                ActionGroup::make([Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),


//                    Action::make('duplicate')
//                        ->label('Duplicate')
//                        ->icon('heroicon-o-document-duplicate') // Use an appropriate icon here
//                        ->action(function ($record) {
//                            // Duplicate the record
//                            $duplicate = $record->replicate(); // Clone the record
//                            $duplicate->save(); // Save the new cloned record
//
//                            // Use Filament's resource URL helper for redirection
//                            return redirect(AreaResource::getUrl('edit', ['record' => $duplicate->id]));
//                        }),
                    ]),



            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()

                ]),

            ]);

    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SitesRelationManager::class
        ];
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Details')->schema([
                    Grid::make(2)->schema([


                        TextEntry::make('name'),
                        TextEntry::make('description')
                            ->badge('true')
                            ->color('info'),

                        TextEntry::make('comments')
                            ->columnSpanFull()

                    ])
                ]),
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'view' => Pages\ViewArea::route('/{record}'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
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
