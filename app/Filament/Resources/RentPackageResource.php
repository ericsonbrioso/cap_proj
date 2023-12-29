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
use App\Models\Package;
use App\Models\User;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Group as ComponentsGroup;
use Filament\Forms\Components\Section as ComponentsSection;
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
use Filament\Tables\Actions\BulkAction;
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
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;

class RentPackageResource extends Resource
{
    protected static ?string $model = RentPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Rented Packages';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = null;

    public static function Form(Form $form): Form
    {
        $packages = Package::get();
        return $form
            
            ->schema([

                Wizard::make([

                    Wizard\Step::make('Choose Package')
                        ->icon('heroicon-m-shopping-bag')
                        ->description('Select Package & Quantity')
                        ->schema([

                        Forms\Components\Select::make('package_id')
                            ->label('Package')
                            ->options(
                               $packages->mapWithKeys(function (Package $packages) {
                                   return [$packages->id => sprintf($packages->name)];
                               })
                               )
                            ->afterStateUpdated(fn ($state, Forms\Set $set)=>
                               $set('unit_price', Package::find($state)?->total ?? 0))
                            ->reactive()
                            ->searchable()
                            ->required(),
                       Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->default(1)
                            ->live()
                            ->dehydrated()
                            ->numeric()
                            ->required(),
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
                        ])->columns(2),
                        
                
                    Wizard\Step::make('Duration')
                        ->icon('heroicon-m-calendar-days')
                        ->description('Select starting & ending date')
                        ->schema([

                        Forms\Components\DateTimePicker::make('delivery')
                            ->label('')
                            ->prefix('Start')
                            ->seconds(false)
                            ->minDate(now()->subHours(14)),
                        Forms\Components\DateTimePicker::make('return')->after('delivery')
                            ->label('')
                            ->prefix('End')  
                            ->required()
                            ->seconds(false)
                            ->minDate(now()),
                        Forms\Components\Select::make('type')
                            ->label('Choose Type')
                            ->options([
                                'pickup' => 'Pickup',
                                'delivery' => 'Delivery',
                            ])
                            ->required(),
                        ])->columns(2),

                    Wizard\Step::make('Client Details')
                        ->icon('heroicon-m-user')
                        ->description('Fill out form')
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
                                ->maxLength(255)
                                ->default(auth()->check() ? auth()->user()->address : null),
                            PhoneInput::make('contact')
                                ->required()
                                ->disallowDropdown()
                                ->defaultCountry('Philippines')
                                ->default(auth()->check() ? auth()->user()->contact : null),
                            
                            ])->columns(1),

                ])->columnSpanFull(),

                        ComponentsGroup::make()
                            ->schema([
                                ComponentsSection::make('Additional Fee')
                                    ->schema([
                                    Forms\Components\TextInput::make('delivery_fee')
                                        ->label('Delivery Fee')
                                        ->numeric()
                                        ->prefix('₱'),
                                ])      
                        ])
                        ->columns(1)
                        ->visible(auth()->user()->isAdmin()),
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
                    'checkout' => 'warning',
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'info',
                    'for-delivery' => 'success',
                    'for-return' => 'success',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                    }),
            Tables\Columns\TextColumn::make('delivery') 
                ->label('Start of Rental')
                ->searchable(),
            Tables\Columns\TextColumn::make('return')
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
                EditAction::make()
                    ->label('')
                    ->visible(fn ($record) => $record->status === 'pending' && auth()->user()->isAdmin()),
                ViewAction::make()
                    ->label(''),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->visible(fn ($record) => $record->status === 'checkout' && auth()->user() && !auth()->user()->isAdmin()),
                Tables\Actions\ActionGroup::make([
                    
                    Tables\Actions\EditAction::make()
                        ->color('info')
                        ->label('Edit Rent')
                        ->visible(fn ($record) => $record->status === 'checkout' && auth()->user() && !auth()->user()->isAdmin()),
                    Action::make('Confirm Checkout')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(function (RentPackage $rent, array $data): void {
                            $rent->status = 'pending';
                            $rent->save();

                            Notification::make()
                                ->title('Done confirmation')
                                ->body('Waiting for approval')
                                ->duration(3000)
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->status === 'checkout' && auth()->user() && !auth()->user()->isAdmin())
                        ->requiresConfirmation(),

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
                                        'for-return' => 'For-return',
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
                            
                            Action::make('For Return')
                                    ->icon('heroicon-m-truck')
                                    ->color('warning')
                                    ->action(function (RentPackage $rent, array $data): void {
                                            $rent->status = 'for-return';
                                            $rent->save();
                                            $rent->save();
        
                                            Notification::make()
                                                ->title('Status Updated')
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
                                        ->visible(fn ($record) => $record->status === 'for-return' && auth()->user()->isAdmin()),

                            Action::make('Cancel Rent')
                                ->icon('heroicon-m-x-circle')
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
                                                    ->image()
                                                    ->label('Upload Photos:')
                                                    ->multiple()
                                                    ->preserveFilenames(),
                                            ])
                                ])
                                ->slideOver()
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
                                }),
                            
                            Action::make('Edit review')
                                ->icon('heroicon-m-pencil-square')
                                ->color('primary')
                                ->form([

                                 Forms\Components\Group::make()
                                    ->schema([
                                        RatingStar::make('rating')
                                            ->label('Overall Rating')
                                            ->default(function ($record) {
                                                return $record->rating; // Set the initial value for editing
                                         }),
                                        Forms\Components\Textarea::make('comment')
                                            ->label('Comment')
                                            ->default(function ($record) {
                                                return $record->comment; // Set the initial value for editing
                                            })
                                        ->hint(str('Type your experience in our rental service')->inlineMarkdown()->toHtmlString())
                                        ->hintIcon('heroicon-m-question-mark-circle'),
                                    ]),
                                    Forms\Components\Group::make()
                                            ->schema([

                                                Forms\Components\Fileupload::make('image')
                                                    ->image()
                                                    ->label('Upload Photos:')
                                                    ->multiple()
                                                    ->preserveFilenames()
                                                    ->default(function ($record) {
                                                        return $record->comment; // Set the initial value for editing,
                                                    })
                                                ]),
                                ])
                                ->slideOver()
                                ->visible(fn ($record) => $record->status === 'completed' && auth()->user() && !auth()->user()->isAdmin())
                                
                                
                ])
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->requiresConfirmation()
                        ->action(fn (RentPackage $records) => $records->each->delete())
                        ->visible(auth()->user() && !auth()->user()->isAdmin()),
            
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
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
                            ->badge(),
                        TextEntry::make('user.name')
                            ->label('Client Name:')
                            ->icon('heroicon-m-user'),
                        TextEntry::make('contact')
                            ->label('Contact Number:')
                            ->icon('heroicon-m-phone'),
                        TextEntry::make('delivery')
                            ->label('Start of rental:')
                            ->icon('heroicon-m-truck'),
                        TextEntry::make('address')
                            ->label('Address:')
                            ->icon('heroicon-m-map-pin'),
                        TextEntry::make('return')
                            ->label('End of rental:')
                            ->icon('heroicon-m-truck'),
                    ]),
                ]),
       

            Group::make()
            ->schema([

                 Section::make('Package Details')
                    ->schema([
                    Split::make([
                        Grid::make(2)
                            ->schema([
                        Group::make([
                            
                            TextEntry::make('package.name')
                                ->weight(FontWeight::Bold)
                                ->label('Package Name:'),
                            ImageEntry::make('package.image')
                                ->hiddenlabel(),
                        ]),
                        
                        Group::make([
                            TextEntry::make('package.total')
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
                    ]),
                ])
                    
            ]),
            ]),

            Group::make()
            ->schema([

                 Section::make('Other Fees:')
                    ->schema([
                    Split::make([
                        Grid::make(3)
                            ->schema([
                        Group::make([
                            TextEntry::make('type')
                                ->label('Type:'),
                            TextEntry::make('delivery_fee')
                                ->label('Transportation Fee:')
                                ->prefix('₱ '),
                            ])
                        ]),
                    ])
                    
                ]),
            ]),

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
    
    public static function getnavigationGroup(): string
    {
        // Check if the user is an admin
        if (Auth::check() && Auth::user()->isAdmin()) {
            // Show the label for admins
            return 'Renting Management';
        }

        // For regular users or other conditions, you can return a default label
        return 'Rents';
    }
}
