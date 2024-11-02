<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WellResource\Pages;
use App\Filament\Resources\WellResource\RelationManagers;
use App\Models\Area;
use App\Models\Site;
use App\Models\Well;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use function Laravel\Prompts\multiselect;

class WellResource extends Resource
{
    protected static ?string $model = Well::class;
    protected static ?string $navigationGroup = 'Well Management';
    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Add Well Details')
                            ->schema([
                                Forms\Components\grid::make('2')
                                    ->schema([
                                        Forms\Components\TextInput::make('lease')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('chemical')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('chemical_type')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('rate')
                                            ->numeric()
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('based_on')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('injection_point')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\MarkdownEditor::make('Comments')
//                                        ->required()
                                            ->columnSpanFull()
                                        ->maxLength(255),

                                    ])

                            ]),

                    ]),Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Select Site and Status')
                            ->schema([
                                Forms\Components\grid::make('1')
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
                                                ->find($value)?->name),
                                        Forms\Components\Toggle::make('is_published')
                                            ->label('Status')
                                            ->default(true)
                                            ->required(),
                                    ])

                            ]),
                    ]),




            ]);
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
        ->schema([
            Tabs::make('Well Usage') // Organized sections into tabs for better navigation
            ->columnSpanFull() // Ensures tabs take the full width for a more consistent layout
            ->tabs([
                Tab::make('Add Well Details')
                    ->schema([
                        Section::make('Add Well Details')
                            ->columns(2) // Set columns for better width utilization
//                            ->color('primary') // Apply color to make sections visually distinct
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('lease')
                                            ->label('Lease')
                                            ->color('success')
                                            ->badge('success') // Added badge with color success
                                            ->columnSpan(1),
                                        TextEntry::make('chemical')
                                            ->label('Chemical')
                                            ->color('info')
                                            ->badge('info') // Added badge with color info
                                            ->columnSpan(1),
                                        TextEntry::make('chemical_type')
                                            ->label('Chemical Type')
                                            ->color('warning')
                                            ->badge('warning') // Added badge with color warning
                                            ->columnSpan(1),
                                        TextEntry::make('rate')
                                            ->label('Rate')
                                            ->numeric()
                                            ->color('danger')
                                            ->badge('danger') // Added badge with color danger
                                            ->columnSpan(1),
                                        TextEntry::make('based_on')
                                            ->label('Based On')
                                            ->color('primary')
                                            ->badge('primary') // Added badge with color primary
                                            ->columnSpan(1),
                                        TextEntry::make('injection_point')
                                            ->label('Injection Point')
                                            ->badge('primary') // Added badge with color primary
                                            ->color('secondary') // Added badge with color secondary
                                            ->columnSpan(1),
                                        TextEntry::make('comments')
                                            ->label('Comments')
                                            ->color('dark')
                                            ->badge('dark') // Added badge with color dark
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
                Tab::make('Site and Status')
                    ->schema([
                        Section::make('Select Site and Status')
                            ->columns(1) // Set columns to make the section span the full width
//                            ->color('primary')
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('site.location')
                                            ->label('Site')
                                            ->color('success')
                                            ->badge('success') ,// Added badge with color success
//                                            ->relationship('site', 'location')
//                                            ->searchable()
//                                            ->preload(),
                                            TextEntry::make('is_published')
                                                ->label('Status')
                                                ->default(true)
                                                ->color('info')
                                                ->badge('info') // Added badge with color info
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make(),
//                CreateAction::make()
                // ...
            ])
            ->columns([
                Tables\Columns\TextColumn::make('lease')
                    ->searchable(),
                Tables\Columns\TextColumn::make('chemical')
                    ->searchable(),
                Tables\Columns\TextColumn::make('chemical_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('based_on')
                    ->searchable(),
                Tables\Columns\TextColumn::make('injection_point')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comments')
                    ->searchable(),
                Tables\Columns\TextColumn::make('site.location')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->Label('Status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->alignRight(true)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate') // Use an appropriate icon here
                    ->action(function ($record) {
                        // Duplicate the record
                        $duplicate = $record->replicate(); // Clone the record
                        $duplicate->save(); // Save the new cloned record

                        // Use Filament's resource URL helper for redirection
                        return redirect(WellResource::getUrl('edit', ['record' => $duplicate->id]));
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
            RelationManagers\WellUsageRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWells::route('/'),
            'create' => Pages\CreateWell::route('/create'),
            'view' => Pages\ViewWell::route('/{record}'),
            'edit' => Pages\EditWell::route('/{record}/edit'),
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
