<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages;
use App\Filament\Resources\SiteResource\RelationManagers;
use App\Filament\Resources\SiteResource\RelationManagers\WellsRelationManager;
use App\Models\Area;
use App\Models\Site;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;
    protected static ?string $recordTitleAttribute = 'location';
    protected static ?string $navigationGroup = "Location Management";
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

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
        return $table
            ->headerActions([
                ExportAction::make(),

                // ...
            ])
            ->columns([
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
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
                    ->query(fn (Builder $query): Builder => $query->where('is_published', true)),
                Tables\Filters\SelectFilter::make('area_id')
                    ->label('Related to Area')
                    ->searchable()
                    ->preload()
                    ->relationship('area', 'name'),
                Filter::make('created_at')
                    ->default(now())
                    ->form([
                        DatePicker::make('created_from')

                        ->native(false),
                        DatePicker::make('created_until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->selectable()
            ->actions([
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate') // Use an appropriate icon here
                    ->action(function ($record) {
                        // Duplicate the record
                        $duplicate = $record->replicate(); // Clone the record
                        $duplicate->save(); // Save the new cloned record

                        // Use Filament's resource URL helper for redirection
                        return redirect(SiteResource::getUrl('edit', ['record' => $duplicate->id]));
                    }),
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
