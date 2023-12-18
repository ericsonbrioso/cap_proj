<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentResource\Pages;
use App\Filament\Resources\EquipmentResource\RelationManagers;
use App\Filament\Resources\EquipmentResource\RelationManagers\RentRelationManager;
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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\ActionGroup;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Layout\Split as LayoutSplit;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                        Forms\Components\Section::make('Equipment Details')
                            ->schema([

                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Select::make('type_id')
                                    ->options(Type::all()->pluck('name', 'id'))
                                    ->label('Equipment Type')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Textarea::make('description')
                                    ->columnSpan('full'),
                                
                            ])->columns(2),
                    Forms\Components\Group::make()
                            ->schema([
                        Forms\Components\Section::make()
                            ->schema([

                                Forms\Components\TextInput::make('price')
                                    ->label('Unit Price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₱'),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Stocks')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('days')
                                    ->label('Days')
                                    ->required()
                                    ->numeric(),
        
                                Forms\Components\Select::make('status')
                                    ->label('Availability')
                                     ->options([
                                        'available' => 'Available',
                                        'unavailable' => 'Unvailable',
                                    ]), 
                                
                            ]),
                        ]),

                    Forms\Components\Group::make()
                            ->schema([
                        Forms\Components\Section::make()
                            ->schema([

                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->preserveFilenames(),        

                            ])
                        ]),
                ]);
    }
    public static function table(Table $table): Table
    {
        return $table
        ->contentGrid([
            'md' => 2,
            'xl' => 2,
        ])
        ->columns([
            
            LayoutSplit::make([
                ImageColumn::make('image')
                    ->size(150),

                Stack::make([

                TextColumn::make('name')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description'),   
                    ]),

                Stack::make([

                    TextColumn::make('status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state)
                        {
                        'available' => 'success',
                        'unavailable' => 'warning',
                        }),
                    TextColumn::make('price')
                        ->label('Rate Per day')
                        ->prefix('₱')
                        ->sortable(),
                   // RatingStarColumn::make('rating')
                ])
                
            ]), 
    ])
            ->filters([
                //
            ])
            ->actions([
        
                    ViewAction::make(),
                    EditAction::make(),

            ])
            ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                    
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
    
    public static function infolist(Infolist $infolist): Infolist
    {
    return $infolist
        ->schema([
                Section::make('Equipment Details')
                    ->schema([
                    Split::make([
                        Grid::make(3)
                            ->schema([
                            Group::make([
                                TextEntry::make('name')
                                    ->label('Equipment Name:')
                                    ->weight(FontWeight::Bold),
                                ImageEntry::make('image')
                                    ->label('')
                                    ->size(200),
                            ]),

                            Group::make([
                                TextEntry::make('type.name')
                                    ->label('Type:'),
                                TextEntry::make('description')
                                    ->label('Description:'),
                                TextEntry::make('status')
                                    ->label('Status:')
                                    ->badge()
                                    ->color('success'),
                            ]),

                            Group::make([
                                TextEntry::make('price')
                                    ->label('Unit Price:'),
                                TextEntry::make('quantity')
                                    ->label('Stocks:'),
                                
                            ])
                                
                        ]),
                    ])
                    
                ]),
        ]);
}

    public static function getRelations(): array
    {
        return [
            RentRelationManager::class,
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
