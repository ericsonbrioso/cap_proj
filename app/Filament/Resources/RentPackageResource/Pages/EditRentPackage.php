<?php

namespace App\Filament\Resources\RentPackageResource\Pages;

use App\Filament\Resources\RentPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRentPackage extends EditRecord
{
    protected static string $resource = RentPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
