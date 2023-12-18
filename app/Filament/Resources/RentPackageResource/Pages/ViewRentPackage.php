<?php

namespace App\Filament\Resources\RentPackageResource\Pages;

use App\Filament\Resources\RentPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRentPackage extends ViewRecord
{
    protected static string $resource = RentPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
