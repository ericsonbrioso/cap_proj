<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageResource\Pages;
use App\Filament\Resources\PackageResource\RelationManagers;
use App\Filament\Resources\PackageResource\RelationManagers\RentPackageRelationManager;
use App\Models\Equipment;
use App\Models\Package;
use App\Models\RentPackage;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\ImageEntry;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use IbrahimBougaoua\FilamentRatingStar\Actions\RatingStar;
use IbrahimBougaoua\FilamentRatingStar\Columns\RatingStarColumn;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Inventory Management';

    public static function form(Form $form): Form
    {
        $equipments = Equipment::get();
        return $form
            ->schema([
            
                Forms\Components\Group::make()
                    ->schema([

                        Forms\Components\Section::make()
                            ->schema([

                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Select::make('status')
                                    ->label('Availability')
                                    ->options([
                                        'available' => 'Available',
                                        'unavailable' => 'Unvailable',
                                    ])
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->columnSpan('full'),
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->preserveFilenames()
                                    ->openable()
                                    ->columnSpan('full'),
                                //RatingStar::make('rating')
                                    //->label('Rating')

                            ])->columns(2)
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        Section::make('Equipments')
                        ->schema([
                            
                            Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([

                            Forms\Components\Select::make('equipment_id')
                                ->label('Equipment')
                                ->options(
                                    $equipments->mapWithKeys(function (Equipment $equipment) {
                                        return [$equipment->id => sprintf('%s (stock %s)', $equipment->name, $equipment->quantity)];
                                    })
                                    )
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(fn ($state, Forms\Set $set)=>
                                      $set('unit_price', Equipment::find($state)?->price ?? 0))
                                ->disableOptionWhen(function ($value, $state, Get $get) {
                                    return collect($get('../*.equipment_id'))
                                        ->reject(fn($id) => $id == $state)
                                        ->filter()
                                        ->contains($value);
                                })
                                ->required(),
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(),
                            Forms\Components\TextInput::make('quantity')
                                ->integer()
                                ->default(1)
                                ->required()
                                ->live()
                                ->dehydrated(),
                            Forms\Components\Placeholder::make('total_price')
                                ->label('Total Price')
                                ->content(function ($get) {
                                    $quantity = (float)$get('quantity');
                                    $unit_price = (float)$get('unit_price');
    
                                    if ($quantity !== null && $unit_price !== null) {
                                        return $quantity * $unit_price;
                                    }
                                    return 0;
                                }),      
                        ])
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::updateTotals($get, $set);
                        })
                        ->deleteAction(
                            fn(Action $action) => $action->after(fn(Get $get, Set $set) => self::updateTotals($get, $set)),
                        )
                        ->reorderable(false)
                        ->columns(2),
                        
                        ]),

                        Forms\Components\Group::make()
                            ->schema([
                
                            Section::make('Total')
                                ->schema([
                                
                             Forms\Components\TextInput::make('subtotal')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('$')
                                    ->afterStateHydrated(function (Get $get, Set $set) {
                                        self::updateTotals($get, $set);
                                    }),
                    ]),
                ]),  
                    ]),
                
            ]);
            
    }

    public static function updateTotals(Get $get, Set $set): void
    {
    // Retrieve all selected products and remove empty rows
    $selectedEquipments = collect($get('items'))->filter(fn($item) => !empty($item['equipment_id']) && !empty($item['quantity']));
 
    // Retrieve prices for all selected products
    $prices = Equipment::find($selectedEquipments->pluck('equipment_id'))->pluck('price', 'id');
 
    // Calculate subtotal based on the selected products and quantities
    $subtotal = $selectedEquipments->reduce(function ($subtotal, $equipment) use ($prices) {
        return $subtotal + ($prices[$equipment['equipment_id']] * $equipment['quantity']);
    }, 0);
 
    // Update the state with the new values
    $set('subtotal', number_format($subtotal, 2, '.', ''));
    }

    public static function table(Table $table): Table
    {
        return $table
        ->contentGrid([
            'md' => 2,
            'xl' => 5,
        ])
        ->columns([
            
            Split::make([
                stack::make([

                ImageColumn::make('image')
                    ->size(165),

                Split::make([
                    TextColumn::make('name')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state)
                    {
                            'available' => 'success',
                            'unavailable' => 'warning',
                    }),
                ]),
                
                TextColumn::make('price')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('Rate Per day')
                    ->prefix('₱ ')
                    ->color('warning')
                    ->sortable(),
                
                Split::make([

                RatingStarColumn::make('rentpackage_avg_rating')
                    ->avg('rent', 'rating'),
                TextColumn::make('rentpackage_avg_rating')
                    ->avg('rentpackage', 'rating')
                    ->suffix('/5')
                    ->color('gray')
                    ->size(TextColumnSize::ExtraSmall)
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: '.',
                    ),
                ]),

                TextColumn::make('rentpackage_count')->counts('rentpackage')
                    ->suffix(' Ratings')
                    ->color('gray')
                    ->size(TextColumnSize::ExtraSmall),
                TextColumn::make('quantity')
                    ->suffix(' Stocks')
                    ->color('gray')
                    ->size(TextColumnSize::ExtraSmall),

                ])->space(1),
                
            ]),  
            
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RentPackageRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            'view' => Pages\ViewPackage::route('/{record}'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    } 
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
