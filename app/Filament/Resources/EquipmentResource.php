<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentResource\Pages;
use App\Filament\Resources\EquipmentResource\RelationManagers;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Type;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Inventory';

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
                                Forms\Components\Select::make('type_id')
                                    ->options(Type::all()->pluck('name', 'id'))
                                    ->label('Equipment Type')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\MarkdownEditor::make('description')
                                    ->columnSpan('full'),

                            ])->columns(2),

                        
                        Forms\Components\Section::make()
                            ->schema([  

                                Forms\Components\TextInput::make('price')
                                    ->label('Original Cost')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₱'),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Stocks')
                                    ->required()
                                    ->numeric(),

                            ])->columns(2)
                    ]),

                Forms\Components\Group::make()
                    ->schema([

                        Forms\Components\Section::make('Status')
                            ->schema([  

                                Forms\Components\Radio::make('condition')  
                                    ->label('Cuurent Condition')    
                                    ->required()
                                    ->options([
                                        'good' => 'Good',
                                        'fair' => 'Fair',
                                        'poor' => 'Poor',
                                ]),

                                Forms\Components\Select::make('status')
                                    ->label('Availability')
                                    ->options([
                                        'available' => 'Available',
                                        'unavailable' => 'Unvailable',
                                    ]),
             
                         ])->columns(2),

                        Forms\Components\Section::make('Insert Image')
                            ->schema([  

                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->preserveFilenames(),
          
                        ])->collapsible()
                    ])
                ]); 

    }
    public static function table(Table $table): Table
    {
        return $table
        ->columns([
                      
            ImageColumn::make('image')
                ->circular(),
            TextColumn::make('name')
                ->weight(FontWeight::Bold)
                ->searchable()
                ->sortable(),
            TextColumn::make('type.name')
                ->label('Type')
                ->searchable(),
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state)
                    {
                    'available' => 'success',
                    'unavailable' => 'warning',
                    }),
            TextColumn::make('price')
                ->prefix('₱')
                 ->sortable(),
            TextColumn::make('condition')
                ->badge()
                ->color(fn (string $state): string => match ($state)
                    {
                    'good' => 'success',
                    'fair' => 'warning',
                    'poor' => 'danger',
                    }),
            
            TextColumn::make('quantity')
                ->numeric()
                ->sortable(),
             TextColumn::make('created_at')
                ->icon('heroicon-m-calendar-days')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->icon('heroicon-m-calendar-days')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
    ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
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
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'view' => Pages\ViewEquipment::route('/{record}'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }  
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
