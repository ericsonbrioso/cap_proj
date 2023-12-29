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
use Illuminate\Support\Facades\Auth;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Inventory';

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
                                        return [$equipment->id => sprintf($equipment->name)];
                                    })
                                    )
                                
                                ->disableOptionWhen(function ($value, $state, Get $get) {
                                        return collect($get('../*.product_id'))
                                            ->reject(fn($id) => $id == $state)
                                            ->filter()
                                            ->contains($value);
                                    })
                                ->required(),
                            Forms\Components\TextInput::make('quantity')
                                ->integer()
                                ->default(1)
                                ->required(),    
                            ]) 
                            ->live()
                            // After adding a new row, we need to update the totals
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            })
                            // After deleting a row, we need to update the totals
                            ->deleteAction(
                                fn(Action $action) => $action->after(fn(Get $get, Set $set) => self::updateTotals($get, $set)),
                            )
                            // Disable reordering
                            ->reorderable(false)
                            ->columns(2)  
                        ]),
                    ]),

            Section::make()
                ->columns(1)
                ->maxWidth('1/2')
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->numeric()
                        // Read-only, because it's calculated
                        ->readOnly()
                        ->prefix('$')
                        // This enables us to display the subtotal on the edit page load
                        ->afterStateHydrated(function (Get $get, Set $set) {
                            self::updateTotals($get, $set);
                        }),
                    Forms\Components\TextInput::make('total')
                        ->numeric()
                        // Read-only, because it's calculated
                        ->readOnly()
                        ->prefix('$')
                ])
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
    $set('total', number_format($subtotal, 2, '.', '')); // Total does not include taxes
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
                
                TextColumn::make('total')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
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
