<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\RentResource;
use App\Models\Rent;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
 
class CalendarWidget extends FullCalendarWidget
{
 
    public function fetchEvents(array $fetchInfo): array
    {
        return Rent::query()
            ->where('date_of_delivery', '>=', $fetchInfo['start'])
            ->where('date_of_pickup', '<=', $fetchInfo['end'])
            ->get()
            ->map(
                fn (Rent $event) => [
                    'title' => $event->rent_number,
                    'start' => $event->date_of_delivery,
                    'end' => $event->date_of_pickup,
                    'url' => RentResource::getUrl(name: 'view', parameters: ['record' => $event]),
                    'shouldOpenUrlInNewTab' => true
                ]
            )
            ->all();
    }

}
