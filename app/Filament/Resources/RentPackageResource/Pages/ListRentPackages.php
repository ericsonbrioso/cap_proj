<?php

namespace App\Filament\Resources\RentPackageResource\Pages;

use App\Filament\Resources\RentPackageResource;
use App\Models\RentPackage;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords\Tab as ListRecordsTab;
use Illuminate\Support\Facades\Auth;

class ListRentPackages extends ListRecords
{
    protected static string $resource = RentPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        $tabs = [];

        if ($isAdmin) {
           // $tabs['all'] = ListRecordsTab::make('All rents')->badge(RentPackage::count());

            
            $statuses = ['pending', 'approved', 'rejected', 'for-delivery', 'for-return', 'completed', 'cancelled'];

            foreach ($statuses as $status) {
                $statusCount = RentPackage::where('status', $status)->count();
                $tabs[$status] = ListRecordsTab::make(ucfirst($status))
                    ->badge($statusCount)
                    ->modifyQueryUsing(fn ($query) => $query->where('status', $status));

            }

        } elseif ($user) {
            //$tabs['user'] = ListRecordsTab::make('Your Rents')->badge(
               //Rent::where('user_id', $user->id)->count()
           // );

            $statuses = ['checkout', 'pending', 'approved', 'rejected', 'for-delivery', 'for-return', 'completed', 'cancelled'];

            foreach ($statuses as $status) {
                $statusCount = RentPackage::where('user_id', $user->id)
                    ->where('status', $status)
                    ->count();

                $tabs[$status] = ListRecordsTab::make(ucfirst($status))
                    ->badge($statusCount)
                    ->modifyQueryUsing(fn ($query) => $query->where('user_id', $user->id)
                        ->where('status', $status));
            }
        }

        return $tabs;
    }
    
}

