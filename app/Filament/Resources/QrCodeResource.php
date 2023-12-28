<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QrCodeResource\Pages;
use App\Filament\Resources\QrCodeResource\RelationManagers;
use App\Models\QrCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class QrCodeResource extends Resource
{
    protected static ?string $model = QrCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'QrCode';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Equipment Details')
                            ->schema([

                \LaraZeus\Qr\Components\Qr::make('qr_code')
                    ->asSlideOver() 
                    ->optionsColumn('string')
                    ->actionIcon('heroicon-s-qr-code')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->openable()
                    ->preserveFilenames(),
    
            ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('QrCode'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('qr_code')
                    ->label('Equipment Link')
                    ->searchable(),
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
            'index' => Pages\ListQrCodes::route('/'),
            'create' => Pages\CreateQrCode::route('/create'),
            'view' => Pages\ViewQrCode::route('/{record}'),
            'edit' => Pages\EditQrCode::route('/{record}/edit'),
        ];
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
