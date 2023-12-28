<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeResource\Pages;
use App\Filament\Resources\TypeResource\RelationManagers\EquipmentRelationManager;
use App\Models\Type;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\ColorPicker;
use Illuminate\Support\Facades\Auth;

class TypeResource extends Resource
{
    protected static ?string $model = Type::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    protected static ?string $navigationGroup = 'Inventory Management';

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
                                Forms\Components\ColorPicker::make('color')
                                    ->label('Primary Color'),

                            ]),

                    ]),

                    Forms\Components\Group::make()
                    ->schema([

                        Forms\Components\Section::make()
                            ->schema([
                                
                                Forms\Components\Toggle::make('status')
                                    ->label('Visibility')
                                    ->helperText('Enable or Disable Type Visibility'),

                            ]),

                    ]),
            ]);    
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Visibility')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
    
    public static function getRelations(): array
    {
        return [
            EquipmentRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTypes::route('/'),
            'create' => Pages\CreateType::route('/create'),
            'edit' => Pages\EditType::route('/{record}/edit'),
        ];
    } 
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    } 

    public static function getnavigationGroup(): string
    {
        // Check if the user is an admin
        if (Auth::check() && Auth::user()->isAdmin()) {
            // Show the label for admins
            return 'Inventory Management';
        }

        // For regular users or other conditions, you can return a default label
        return 'Equipment & Packages';
    }
}
