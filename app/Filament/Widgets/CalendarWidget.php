<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\RentResource;
use App\Models\Rent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public function fetchEvents(array $fetchInfo): array
    {
        $user = Auth::user();

        // Check if the user is an admin
        if ($user->isAdmin()) {
            // Admin can see all rents
            $events = Rent::query()
                ->where('delivery', '>=', $fetchInfo['start'])
                ->where('return', '<=', $fetchInfo['end'])
                ->get();
        } else {
            // Regular user can see only their rents
            $events = Rent::query()
                ->where('user_id', $user->id)
                ->where('delivery', '>=', $fetchInfo['start'])
                ->where('return', '<=', $fetchInfo['end'])
                ->get();
        }

        return $events->map(
            function (Rent $event) {
                $color = $this->generateColorForRent($event->id);
                
                return [
                    'title' => $event->rent_number,
                    'start' => $event->delivery,
                    'end' => $event->return,
                    'url' => RentResource::getUrl(name: 'view', parameters: ['record' => $event]),
                    'shouldOpenUrlInNewTab' => true,
                    'color' => $color,
                ];
            }
        )->all();
    }

    private function generateColorForRent($rentId): string
    {
        // Generate a consistent color based on the rent ID
        $hash = md5($rentId);
        return '#' . substr($hash, 0, 6);
    }
}
