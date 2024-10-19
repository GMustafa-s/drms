<?php

namespace App\Filament\Resources\SiteResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class WellsRelationManager extends RelationManager
{
    protected static string $relationship = 'Wells';
    public static function getActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create New Well Usage')  // Customize the button label
                ->color('primary')  // Customize the button color
                ->icon('heroicon-o-plus'),  // Add an icon (optional)

            // Custom action example
            Action::make('customReport')
                ->label('Generate Report')
                ->action(function () {
                    // Define the custom action logic here
                    // e.g., generate a report or redirect to another page
                })
                ->icon('heroicon-o-document-report')
                ->requiresConfirmation()  // Optional: Add a confirmation dialog before executing
                ->color('success'),  // Optional: Change the button color
        ];
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Section::make('Add Well Details')
                            ->schema([
                                Forms\Components\grid::make('3')
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
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('based_on')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('injection_point')
                                            ->required()
                                            ->maxLength(255),
                                    ]),
                                Forms\Components\Section::make('Comment and Status')
                                    ->schema([
                                        Forms\Components\grid::make('1')
                                            ->schema([

                                                Forms\Components\MarkdownEditor::make('Comments')
                                                    ->required()
                                                    ->columnSpanFull()
                                                    ->maxLength(255),

                                                Forms\Components\Toggle::make('is_published')
                                                    ->label('Status')
                                                    ->default(true)
                                                    ->required(),
                                            ])

                                    ]),

                            ]),

                    ]),
            ]);
    }

    public function table(Table $table): Table
    {

        return $table

            ->recordTitleAttribute('lease')
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
//                Tables\Columns\TextColumn::make('site.location')
//                    ->numeric()
//                    ->sortable(),
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
//                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
//
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
