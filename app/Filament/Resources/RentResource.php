<?php

namespace App\Filament\Resources;

use App\Enums\RentStatusEnum;
use App\Filament\Resources\RentResource\Pages;
use App\Filament\Resources\RentResource\RelationManagers;
use App\Models\Rent;
use Filament\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
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
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\FormsComponent;
use Filament\Resources\Forms\Components;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneInputColumn;



class RentResource extends Resource
{
    protected static ?string $model = Rent::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Renting';

    public static function Form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Group::make()
                    ->schema([

                        Forms\Components\Section::make()
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
                             Forms\Components\DateTimePicker ::make('date_of_delivery')
                                ->suffixIcon('heroicon-m-calendar-days')
                                ->prefix('Start')  
                                ->required()
                                ->seconds(false)
                                ->native(false)
                                ->minDate(now()->subHours(14)), 
                            Forms\Components\TextInput::make('address')
                                ->label('Complete Address')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\DateTimePicker::make('date_of_pickup')
                                ->suffixIcon('heroicon-m-calendar-days') 
                                ->prefix('End')  
                                ->required()
                                ->seconds(false)
                                ->native(false)
                                ->minDate(now()),
                           PhoneInput::make('contact')
                                ->disallowDropdown()
                                ->required()
                                ->defaultCountry('Philippines'),
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->default('pending')
                                ->options([
                                    'pending' => RentStatusEnum::PENDING->value,
                                    'processing' => RentStatusEnum::PROCESSING->value,
                                    'completed' => RentStatusEnum::COMPLETED->value,
                                    'cancelled' => RentStatusEnum::CANCELLED->value,
                                ])
                                ->disabled()
                                    
                            ])->columns(2),

                    ])->columnSpanFull(),
                    Forms\Components\Group::make()
                        ->schema([

                        Forms\Components\Section::make('Packages')
                            ->schema([

                                Forms\Components\Repeater::make('packageitems')
                                    ->label('')
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
                                ])->columns(2),
                            ])
                        ]),

                    Forms\Components\Group::make()
                        ->schema([

                        Forms\Components\Section::make('Custom Package')
                            ->schema([
                        
                                Forms\Components\Repeater::make('items')
                                    ->label('')
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
                            ])->columns(2),
                        ])
                    ]),
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
                Tables\Columns\TextColumn::make('contact'),
                Tables\Columns\TextColumn::make('address'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state)
                        {
                        'pending' => 'info',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        }),
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
                Tables\Actions\ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('Edit Status')
                            ->icon('heroicon-m-pencil-square')
                            ->form([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options(RentStatusEnum::class)
                            ])
                                ->action(function (Rent $rent, array $data): void {
                                    $rent->status = $data['status'];
                                    $rent->save();

                                    Notification::make()
                                        ->title('Status Updated')
                                        ->duration(3000)
                                        ->success()
                                        ->send();
                                })
                                ->visible(fn ($data) => auth()->user()->isAdmin()),
                    Action::make('Cancel Order')
                            ->icon('heroicon-m-trash')
                            ->color('danger')
                            ->action(function (Rent $rent): void {
                                $rent->status = RentStatusEnum::CANCELLED->value;
                                $rent->save();

                                Notification::make()
                                ->title('Order Cancelled')
                                ->duration(3000)
                                ->success()
                                ->send();
                            })
                            ->visible(fn ($data) => auth()->user() && !auth()->user()->isAdmin()),
                ])
                
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
            'view' => Pages\ViewRent::route('/{record}'),
            'edit' => Pages\EditRent::route('/{record}/edit'),
        ];
    } 
    
    public static function getNavigationBadge(): ?string
{
    $user = auth()->user();

    if ($user) {
        $userRentCount = $user->isAdmin()
            ? Rent::count() 
            : Rent::where('user_id', $user->id)->count(); 

        return $userRentCount > 0 ? (string) $userRentCount : null;
    }

    return null;
}

}
