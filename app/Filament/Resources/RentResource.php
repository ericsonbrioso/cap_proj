<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentResource\Pages;
use App\Models\Rent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Select;
use App\Models\Equipment;
use App\Models\Package;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\FormsComponent;
use Filament\Resources\Forms\Components;
use Filament\Forms\Components\TimePicker;



class RentResource extends Resource
{
    protected static ?string $model = Rent::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Renting';

    public static function Form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Client Details')
                        ->schema([
                            
                            Forms\Components\TextInput::make('rent_number')
                                ->label('Rent Number')
                                ->default('RN-'. random_int(100000, 999999))
                                ->disabled()
                                ->dehydrated()
                                ->required(),
                            Forms\Components\Hidden::make('user_id')
                                ->default(auth()->check() ? auth()->user()->id : null)
                                ->required(),
                            Forms\Components\TextInput::make('address')
                                ->label('Complete Address')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('contact')
                                ->label('Contact Number')
                                ->type('number')
                                ->required()
                                ->maxValue(11),
                            
                               
                        ]),

                    Wizard\Step::make('Rent Package')
                            ->schema([
    
                                Forms\Components\Repeater::make('packageitems')
                                    ->label('Items')
                                    ->relationship()
                                    ->schema([  
    
                                        Forms\Components\Select::make('package_id')
                                            ->label('Package')
                                            ->options(Package::query()->pluck('name','id'))
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, Forms\Set $set)=>
                                                $set('unit_price', Package::find($state)?->price ?? 0))
                                            ->searchable()
                                            ->nullable(),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Quantity') 
                                            ->default(1)
                                            ->live()
                                            ->dehydrated()
                                            ->numeric(),
                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Unit Price')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(),
                                        Forms\Components\Placeholder::make('total_price')
                                            ->label('Total Price')
                                            ->content(function ($get) {
                                                return $get('quantity') * $get('unit_price');}),      
                                    ])->columns(4),
                                ]),
                            Wizard\Step::make('Custom Package')
                                ->schema([
        
                                    Forms\Components\Repeater::make('items')
                                        ->label('Items')
                                        ->relationship()
                                        ->schema([  
        
                                            Forms\Components\Select::make('equipment_id')
                                                ->label('Equipment')
                                                ->options(Equipment::query()->pluck('name','id'))
                                                ->reactive()
                                                ->afterStateUpdated(fn ($state, Forms\Set $set)=>
                                                    $set('unit_price', Equipment::find($state)?->price ?? 0))
                                                ->searchable(),
                                            Forms\Components\TextInput::make('quantity')
                                                ->label('Quantity')
                                                ->default(1)
                                                ->live()
                                                ->dehydrated()
                                                ->numeric(),
                                            Forms\Components\TextInput::make('unit_price')
                                                ->label('Unit Price')
                                                ->numeric()
                                                ->disabled()
                                                ->dehydrated(),
                                            Forms\Components\Placeholder::make('total_price')
                                                ->label('Total Price')
                                                ->content(function ($get) {
                                                    return $get('quantity') * $get('unit_price');}),      
                                        ])->columns(4),
                                    ]),

                        Wizard\Step::make('Set Date')
                            ->schema([
                                    
                            Forms\Components\DateTimePicker ::make('date_of_delivery')
                                ->suffixIcon('heroicon-m-calendar-days')
                                ->prefix('Start')  
                                ->required()
                                ->seconds(false)
                                ->native(false)
                                ->minDate(now()->subHours(14)),
                            Forms\Components\DateTimePicker::make('date_of_pickup')
                                ->suffixIcon('heroicon-m-calendar-days') 
                                ->prefix('End')  
                                ->required()
                                ->seconds(false)
                                ->native(false)
                                ->minDate(now()),
                                        
                                ]),
                                
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();

    return $table
        ->modifyQueryUsing(function (Builder $query) use ($user) {
            if ($user && !$user->isAdmin()) {   
                $query->where('user_id', $user->id);
            }
        })    
            ->columns([
                Tables\Columns\TextColumn::make('rent_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_delivery') 
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_pickup')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListRents::route('/'),
            'create' => Pages\CreateRent::route('/create'),
            'edit' => Pages\EditRent::route('/{record}/edit'),
        ];
    } 
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    } 
     
}
