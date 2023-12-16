<?php

namespace App\Filament\Resources\EquipmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use IbrahimBougaoua\FilamentRatingStar\Columns\RatingStarColumn;
use Filament\Tables\Columns\Summarizers\Average;

class RentRelationManager extends RelationManager
{
    protected static string $relationship = 'Rent';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('rent_number')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('rent_number')
            ->columns([
                Tables\Columns\TextColumn::make('user.name'),
                RatingStarColumn::make('rating')
                    ->summarize([
                        Average::make()
                            ->label('Average Rating'),
                    ]),
                Tables\Columns\TextColumn::make('comment'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
    
}
