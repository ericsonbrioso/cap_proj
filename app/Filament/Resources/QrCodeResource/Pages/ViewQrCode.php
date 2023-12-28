<?php

namespace App\Filament\Resources\QrCodeResource\Pages;

use App\Filament\Resources\QrCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewQrCode extends ViewRecord
{
    protected static string $resource = QrCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
