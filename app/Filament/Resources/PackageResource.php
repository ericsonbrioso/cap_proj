<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageResource\Pages;
use App\Filament\Resources\PackageResource\RelationManagers;
use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ImageColumn;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Renting';

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
                                Forms\Components\TextInput::make('price')
                                    ->label('Original Cost')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₱'),
                                Forms\Components\MarkdownEditor::make('description')
                                    ->columnSpan('full'),

                            ])->columns(2),

                    ]),

                Forms\Components\Group::make()
                    ->schema([

                        Forms\Components\Section::make('Status')
                            ->schema([  

                                Forms\Components\Toggle::make('status')
                                    ->label('Availability')
                                    ->helperText('Unavailable or Available'),
             
                         ])->columns(2),

                        Forms\Components\Section::make('Insert Image')
                            ->schema([  

                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->preserveFilenames()
                                    ->openable(),
          
                        ])->collapsible()
                    ])
                ]); 

    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Split::make([
                    ImageColumn::make('image')
                        ->circular(),
                    TextColumn::make('name')
                        ->weight(FontWeight::Bold)
                        ->searchable()
                        ->sortable(),
                    IconColumn::make('status')->boolean()
                        ->label('status'),
                    TextColumn::make('price')
                        ->prefix('₱')
                        ->sortable(),

                ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    } 
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    } 
}
