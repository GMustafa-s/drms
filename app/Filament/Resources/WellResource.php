<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WellResource\Pages;
use App\Filament\Resources\WellResource\RelationManagers;
use App\Models\Well;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
    protected static ?string $tenantRelationshipName = 'company';

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
                                        Forms\Components\TextInput::make('ppm')
                                            ->label('PPM Rate')
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
                                        ->required()
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
                                            ->relationship('site', 'location')
//                                            ->searchable()
                                            ->native(false)
                                            ->searchable()
                                            ->preload()
//                                            ->multiple()
                                            ->required(),
                                        Forms\Components\Toggle::make('is_published')
                                            ->label('Status')
                                            ->default(true)
                                            ->required(),
                                    ])

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
                Tables\Columns\TextColumn::make('ppm')
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
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\EditAction::make(),
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
//            'view' => Pages\ViewWell::route('/{record}'),
            'create' => Pages\CreateWell::route('/create'),
            'edit' => Pages\EditWell::route('/{record}/edit'),
        ];
    }
}
