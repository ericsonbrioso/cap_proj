<?php

namespace App\Filament\Resources\RentResource\Pages;

use App\Filament\Resources\RentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Rent;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords\Tab as ListRecordsTab;
use Illuminate\Support\Facades\Auth;

class ListRents extends ListRecords
{
    protected static string $resource = RentResource::class;

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
            $tabs['all'] = ListRecordsTab::make('All rents')->badge(Rent::count());

            $statuses = ['pending', 'approved', 'completed', 'cancelled'];

            foreach ($statuses as $status) {
                $statusCount = Rent::where('status', $status)->count();
                $tabs[$status] = ListRecordsTab::make(ucfirst($status))
                    ->badge($statusCount)
                    ->modifyQueryUsing(fn ($query) => $query->where('status', $status));
            }
        } elseif ($user) {
            $tabs['user'] = ListRecordsTab::make('Your Rents')->badge(
                Rent::where('user_id', $user->id)->count()
            );

            $statuses = ['pending', 'approved', 'completed', 'cancelled'];

            foreach ($statuses as $status) {
                $statusCount = Rent::where('user_id', $user->id)
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
