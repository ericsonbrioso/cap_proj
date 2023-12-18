<?php

namespace App\Filament\Resources\PackageResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use IbrahimBougaoua\FilamentRatingStar\Columns\RatingStarColumn;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Support\Enums\Alignment;

use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class RentPackageRelationManager extends RelationManager
{
    protected static string $relationship = 'RentPackage';

    protected static ?string $title = 'Ratings & Reviews';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('rent_number')

            ->contentGrid([
                'md' => 2,
                'xl' => 1,
            ])
            ->columns([

                Split::make([
                    Stack::make([

                        Tables\Columns\TextColumn::make('user.name')
                            ->badge()
                            ->icon('heroicon-m-check-badge')
                            ->color('success'),
                        RatingStarColumn::make('rating')
                            ->summarize(Average::make()->numeric(
                                decimalPlaces: 1,
                                decimalSeparator: '.',
                        )) 
                            ->sortable(),

                        Tables\Columns\TextColumn::make('comment'),
                        Tables\Columns\ImageColumn::make('image')
                            ->size(100)
                            ->limit(5)
                            ->stacked()
                            ->limitedRemainingText(isSeparate: true),

                    ])->space(3),

                        Tables\Columns\TextColumn::make('updated_at')
                            ->label('Recent')
                            ->since()
                            ->alignment(Alignment::End)
                            ->visibleFrom('md')
                            ->sortable(), 
                        
                ]),

            ])
            ->filters([
                SelectFilter::make('rating')
                        ->label('All stars')
                        ->options([
                            '1' => '1 Star',
                            '2' => '2 Stars',
                            '3' => '3 Stars',
                            '4' => '4 Stars',
                            '5' => '5 Stars',
                        ]),
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
