<?php

namespace App\Filament\Resources\EquipmentResource\RelationManagers;

use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use IbrahimBougaoua\FilamentRatingStar\Actions\RatingStar;
use IbrahimBougaoua\FilamentRatingStar\Columns\RatingStarColumn;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Wizard;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneInputColumn;

class RentRelationManager extends RelationManager
{
    protected static string $relationship = 'Rent';

    protected static ?string $title = 'Reviews & Rating';

    protected static ?string $icon = 'heroicon-m-check-badge';

    public function form(Form $form): Form
    {

        return $form
            ->schema([
               //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table

            ->contentGrid([
                'md' => 2,
                'xl' => 1,
            ])
            ->columns([
                
                Split::make([
                    Stack::make([

                        Tables\Columns\TextColumn::make('user.name')
                            ->color('gray'),
                        Tables\Columns\TextColumn::make('rent_number')
                            ->label('Verified Rent')
                            ->icon('heroicon-m-check-badge')
                            ->iconPosition(IconPosition::After)
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
                    ])->space(2),

                        Tables\Columns\TextColumn::make('updated_at')
                            ->label('Recent')
                            ->color('gray')
                            ->since()
                            ->alignment(Alignment::End)
                            ->size(TextColumnSize::ExtraSmall)
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
                //Tables\Actions\CreateAction::make()
                 //   ->label('Add to Cart')
                  //  ->color('warning'),     
            ])
            ->actions([

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    
                ]),
            ])
            ->emptyStateActions([
               // Tables\Actions\CreateAction::make(),
            ]);
    }

    public function isReadOnly(): bool
    {
    return false;
    }
    
}
