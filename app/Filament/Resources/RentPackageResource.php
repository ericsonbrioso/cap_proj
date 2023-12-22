<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentPackageResource\Pages;
use App\Filament\Resources\RentPackageResource\RelationManagers;
use App\Models\RentPackage;
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
use Filament\Forms\Components\TextInput;
use Filament\Forms\FormsComponent;
use Filament\Resources\Forms\Components;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Collection;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneInputColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use IbrahimBougaoua\FilamentRatingStar\Actions\RatingStar;
use IbrahimBougaoua\FilamentRatingStar\Columns\RatingStarColumn;

class RentPackageResource extends Resource
{
    protected static ?string $model = RentPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Rented Packages';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Renting Management';

    public static function Form(Form $form): Form
    {
        $packages = Package::get();
        return $form
            
            ->schema([

                Wizard::make([
                    Wizard\Step::make('Client Details')
                        ->schema([
                   
                            Forms\Components\TextInput::make('rent_number')
                                ->label('Rent Number')
                                ->default('RN-'. random_int(100000, 999999))
                                ->readOnly()
                                ->dehydrated()
                                ->required(),
                            Forms\Components\Hidden::make('user_id')
                                ->default(auth()->check() ? auth()->user()->id : null)
                                ->required(),
                            Forms\Components\TextInput::make('address')
                                ->label('Complete Address')
                                ->required()
                                ->maxLength(255)
                                ->default(auth()->check() ? auth()->user()->address : null)
                                ->readOnly(),
                            PhoneInput::make('contact')
                                ->required()
                                ->disallowDropdown()
                                ->defaultCountry('Philippines')
                                ->default(auth()->check() ? auth()->user()->contact : null),
                            Forms\Components\Select::make('type')
                                ->label('Choose Type')
                                ->options([
                                    'pickup' => 'Pickup',
                                    'delivery' => 'Delivery',
                                ])
                                ->required(),
                            ])->columns(1),

                    Wizard\Step::make('Choose Package')
                        ->schema([

                            Forms\Components\Select::make('package_id')
                                 ->label('Package')
                                 ->options(
                                    $packages->mapWithKeys(function (Package $package) {
                                        return [$package->id => sprintf('%s (stock %s)', $package->name, $package->quantity)];
                                    })
                                    )
                                 ->reactive()
                                 ->afterStateUpdated(fn ($state, Forms\Set $set)=>
                                      $set('unit_price', Package::find($state)?->price ?? 0))
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
                                 ->readOnly()
                                 ->dehydrated()
                                 ->prefix('₱'),
                            Forms\Components\Placeholder::make('total_price')
                                 ->label('Total Price:')
                                 ->content(function ($get) {
                                     $quantity = (float)$get('quantity');
                                     $unit_price = (float)$get('unit_price');
     
                                     if ($quantity !== null && $unit_price !== null) {
                                         return $quantity * $unit_price;
                                     }
                                     return 0;
                                 }),  
                        ]),
                    Wizard\Step::make('Duration')
                        ->schema([

                        Forms\Components\DateTimePicker::make('date_of_delivery')
                            ->label('Scheduled for Rental')
                            ->suffixIcon('heroicon-m-calendar-days')
                            ->prefix('Start')
                            ->required()
                            ->seconds(false)
                            ->minDate(now()->subHours(14))
                            ->hint(str('To be delivered')->inlineMarkdown()->toHtmlString())
                            ->hintIcon('heroicon-m-question-mark-circle'),
                        Forms\Components\DateTimePicker::make('date_of_pickup')->after('date_of_delivery')
                            ->label('End of Rental')
                            ->suffixIcon('heroicon-m-calendar-days') 
                            ->prefix('End')  
                            ->required()
                            ->seconds(false)
                            ->minDate(now()),
                        Forms\Components\TextInput::make('delivery_fee')
                            ->label('Transportation Fee')
                            ->numeric()
                            ->readOnly(fn () => !auth()->user()->isAdmin())
                            ->visible(true)
                            ->hint(str('applied when 10km away from shop (only for delivery)')->inlineMarkdown()->toHtmlString())
                            ->hintIcon('heroicon-m-question-mark-circle')
                            ->default(00.00)
                            ->prefix('₱')
                            ->suffixIcon('heroicon-m-truck'),

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
            Tables\Columns\ImageColumn::make('package.image')
                ->label('')
                ->size(80),
            Tables\Columns\TextColumn::make('package.name')
                ->searchable(),
            Tables\Columns\TextColumn::make('type')
                ->badge()
                ->searchable()
                ->color(fn (string $state): string => match ($state)
                {
                'delivery' => 'info',
                'pickup' => 'info',
                }),
            Tables\Columns\TextColumn::make('rent_number')
                ->label('RN#')
                ->searchable(),
            Tables\Columns\TextColumn::make('quantity')
                ->prefix('Qty. '),
            Tables\Columns\TextColumn::make('unit_price')
                ->prefix('₱ '),
            Tables\Columns\TextColumn::make('total_price')
                ->summarize(Sum::make()->money('PESO'))
                ->prefix('₱ '),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state)
                    {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'info',
                    'for-delivery' => 'success',
                    'for-pickup' => 'success',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                    }),
            Tables\Columns\TextColumn::make('date_of_delivery') 
                ->label('Start of Rental')
                ->searchable(),
            Tables\Columns\TextColumn::make('date_of_pickup')
                ->label('End of Rental')
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
                    Action::make('Edit Status')
                            ->icon('heroicon-m-pencil-square')
                            ->form([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ])
                            ])
                                ->action(function (RentPackage $rent, array $data): void {
                                    $rent->status = $data['status'];
                                    $rent->save();

                                    Notification::make()
                                        ->title('Status Updated')
                                        ->duration(3000)
                                        ->success()
                                        ->send();
                                })
                                ->visible(fn ($record) => $record->status === 'pending' && auth()->user()->isAdmin()),

                            Action::make('Delivery Status')
                                ->icon('heroicon-m-pencil-square')
                                ->form([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'for-delivery' => 'For-delivery',
                                        'for-pickup' => 'For-pickup',
                                    ])
                                ])
                                    ->action(function (RentPackage $rent, array $data): void {
                                        $rent->status = $data['status'];
                                        $rent->save();
    
                                        Notification::make()
                                            ->title('Status Updated')
                                            ->duration(3000)
                                            ->success()
                                            ->send();
                                    })
                                    ->visible(fn ($record) => $record->status === 'approved' && auth()->user()->isAdmin()),
                            
                            Action::make('For Pickup')
                                    ->icon('heroicon-m-truck')
                                    ->color('warning')
                                    ->action(function (RentPackage $rent, array $data): void {
                                            $rent->status = 'for-pickup';
                                            $rent->save();
                                            $rent->save();
        
                                            Notification::make()
                                                ->title('Out for pickup')
                                                ->duration(3000)
                                                ->success()
                                                ->send();
                                        })
                                        ->visible(fn ($record) => $record->status === 'for-delivery' && auth()->user()->isAdmin()),
                            
                            Action::make('Completed')
                                    ->icon('heroicon-m-check-circle')
                                    ->color('success')
                                    ->action(function (RentPackage $rent, array $data): void {
                                            $rent->status = 'completed';
                                            $rent->save();
                                            $rent->save();
        
                                            Notification::make()
                                                ->title('Rent Completed')
                                                ->duration(3000)
                                                ->success()
                                                ->send();
                                        })
                                        ->visible(fn ($record) => $record->status === 'for-pickup' && auth()->user()->isAdmin()),

                            Action::make('Cancel Rent')
                                ->icon('heroicon-m-trash')
                                ->color('danger')
                                ->action(function (RentPackage $rent): void {
                                 
                                    $currentDate = now();
                                    $rentalStartDate = $rent->date_of_delivery;
                                    $daysDifference = $currentDate->diffInDays($rentalStartDate);
                            
                                    if ($daysDifference <= 14) {
                                        // Allow cancellation
                                        $rent->status = 'cancelled';
                                        $rent->save();
                            
                                        Notification::make()
                                            ->title('Rent Cancelled')
                                            ->duration(5000)
                                            ->success()
                                            ->body('The rent has been successfully cancelled.')
                                            ->send();
                                    } else {
                                      
                                        Notification::make()
                                            ->title('Cancellation Not Allowed')
                                            ->duration(5000) 
                                            ->danger() 
                                            ->body('You cannot cancel the rent within 2 week of the rental period.')
                                            ->send();
                                    }
                                })
                                ->visible(fn ($record) => $record->status === 'pending' && auth()->user() && !auth()->user()->isAdmin()),
                            
                            Action::make('To Review')
                                ->icon('heroicon-m-star')
                                ->color('warning')
                                ->form([
                                    Forms\Components\Group::make()
                                        ->schema([

                                            RatingStar::make('rating')
                                                ->label('Overall Rating'),
                                            Forms\Components\Textarea::make('comment')
                                                ->label('Comment')
                                                ->hint(str('type your experience in our rental service')->inlineMarkdown()->toHtmlString())
                                                ->hintIcon('heroicon-m-question-mark-circle'),
                                                                                       
                                        ]),

                                        Forms\Components\Group::make()
                                            ->schema([

                                                Forms\Components\Fileupload::make('image')
                                                    ->label('Upload Photos:')
                                                    ->multiple()
                                                    ->preserveFilenames(),
                                            ])
                                ])
                                ->visible(fn ($record) => $record->status === 'completed' && auth()->user() && !auth()->user()->isAdmin())
                                ->action(function (RentPackage $rent, array $data): void {
                               
                                    $rent->update([
                                        'rating' => $data['rating'],
                                        'comment' => $data['comment'],
                                        'image' => $data['image'],
                                    ]);
                            
                                    Notification::make()
                                        ->title('Review Submitted')
                                        ->duration(3000)
                                        ->success()
                                        ->send();
                                })
                                
                ])
                
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
                Section::make('Rent Details')
                    ->schema([
                    Grid::make(2)
                        ->schema([
                        TextEntry::make('rent_number')
                            ->label('Rent Number:')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('user.name')
                            ->label('Client Name:')
                            ->icon('heroicon-m-user'),
                        TextEntry::make('contact')
                            ->label('Contact Number:')
                            ->icon('heroicon-m-phone'),
                        TextEntry::make('date_of_delivery')
                            ->label('Start of rental: (sheduled for delivery on/before the said date)')
                            ->icon('heroicon-m-truck'),
                        TextEntry::make('address')
                            ->label('Address:')
                            ->icon('heroicon-m-map-pin'),
                        TextEntry::make('date_of_pickup')
                            ->label('End of rental: (retrieval of equipments)')
                            ->icon('heroicon-m-truck'),
                    ]),
                ]),
       

            Group::make()
            ->schema([

                 Section::make('Package Details')
                    ->schema([
                    Split::make([
                        Grid::make(3)
                            ->schema([
                        Group::make([
                            
                            TextEntry::make('package.name')
                                ->weight(FontWeight::Bold)
                                ->label('Package Name:'),
                            ImageEntry::make('package.image')
                                ->hiddenlabel(),
                        ]),
                        
                        Group::make([
                            TextEntry::make('unit_price')
                                ->label('Unit Price:')
                                ->prefix('₱ '),
                            TextEntry::make('quantity')
                                ->label('Quantity:')
                                ->prefix(''),
                            TextEntry::make('total_price')
                                ->label('Total Amount: (Per Day)')
                                ->prefix('₱ ')
                                ->badge()
                                ->color('warning'),
                        ]),

                        Group::make([
                            TextEntry::make('delivery_fee')
                                ->label('Transportation Fee:')
                                ->prefix('₱ '),
                           
                        ])
                    ]),
                                ])
                    
                        ]),
            ])->columnSpanFull(),

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
            'index' => Pages\ListRentPackages::route('/'),
            'create' => Pages\CreateRentPackage::route('/create'),
            'view' => Pages\ViewRentPackage::route('/{record}'),
            'edit' => Pages\EditRentPackage::route('/{record}/edit'),
        ];
    } 
    
    public static function getNavigationBadge(): ?string
    {
    $user = auth()->user();

    if ($user) {
        $userRentCount = $user->isAdmin()
            ? RentPackage::count() 
            : RentPackage::where('user_id', $user->id)->count(); 

        return $userRentCount > 0 ? (string) $userRentCount : null;
    }

    return null;
    }  
}
