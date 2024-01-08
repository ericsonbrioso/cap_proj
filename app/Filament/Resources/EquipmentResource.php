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
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Actions;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ImageColumn;
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
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use IbrahimBougaoua\FilamentRatingStar\Columns\RatingStarColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    protected static ?string $navigationLabel = 'Equipments';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                    Forms\Components\Section::make('Equipment Details')
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
                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Textarea::make('description')
                                    ->columnSpan('full'),
                                
                            ])->columns(3),
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
                                    ->numeric()
                                    ->default(1)
                                    ->readOnly(),
                                
                            ]),
                        ]),

                    Forms\Components\Group::make()
                            ->schema([
                        Forms\Components\Section::make()
                            ->schema([

                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->openable()
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
            'xl' => 5,
        ])
        ->columns([
            
            LayoutSplit::make([
                stack::make([

                ImageColumn::make('image')
                    ->size(165),

                LayoutSplit::make([

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
                
                LayoutSplit::make([

                TextColumn::make('price')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->prefix('₱ ')
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('days')
                    ->suffix(' day')
                    ->color('gray')
                    ->size(TextColumnSize::ExtraSmall),
                    ]),

                LayoutSplit::make([

                RatingStarColumn::make('rent_avg_rating')
                    ->avg('rent', 'rating'),
                TextColumn::make('rent_avg_rating')
                    ->avg('rent', 'rating')
                    ->suffix('/5')
                    ->color('gray')
                    ->size(TextColumnSize::ExtraSmall)
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: '.',
                    ),
                ]),

                TextColumn::make('rent_count')->counts('rent')
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
                SelectFilter::make('type')
                    ->relationship('type', 'name'),
            ])
            ->headerActions([
                Action::make('create')
                ->label('Createa Rent')
                ->link('https://rentasor.tlccs.site/admin/rents'),
                
            ])
            ->actions([
        
                    ViewAction::make(),
                    EditAction::make()

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
                        Grid::make(4)
                            ->schema([
                            Group::make([
                                TextEntry::make('name')
                                    ->label('Equipment Name:')
                                    ->weight(FontWeight::Bold)
                                    ->copyable()
                                    ->copyMessage('Copied!')
                                    ->copyMessageDuration(1500),
                                ImageEntry::make('image')
                                    ->label('')
                                    ->size(200),
                            ]),

                            Group::make([
                                TextEntry::make('code')
                                    ->label('Code:')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('type.name')
                                    ->label('Type:')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('days')
                                    ->label('Days:')
                                    ->weight(FontWeight::Bold),
                                
                            ]),

                            Group::make([
                                TextEntry::make('status')
                                    ->label('Status:')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state)
                                    {
                                        'available' => 'success',
                                        'unavailable' => 'warning',
                                    }),
                                TextEntry::make('price')
                                    ->numeric(
                                        decimalPlaces: 2,
                                        decimalSeparator: '.',
                                        thousandsSeparator: ',',
                                    )
                                    ->prefix('₱ ')
                                    ->label('Unit Price:'),
                                TextEntry::make('quantity')
                                    ->label('Stocks:'),
                                
                                ]),

                            Group::make([
                                   
                                    TextEntry::make('description')
                                        ->label('Description:'),
                                        
                                ]),
                                
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
