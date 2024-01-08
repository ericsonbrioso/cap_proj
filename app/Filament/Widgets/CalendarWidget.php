<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\RentPackageResource;
use App\Filament\Resources\RentResource;
use App\Models\Rent;
use App\Models\RentPackage;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public function fetchEvents(array $fetchInfo): array
    {
        $user = Auth::user();

        // Fetch Rent events
        $rentEvents = $this->fetchRentEvents($user, $fetchInfo);

        // Fetch RentPackage events
        $rentPackageEvents = $this->fetchRentPackageEvents($user, $fetchInfo);

        // Merge and return both sets of events
        return array_merge($rentEvents, $rentPackageEvents);
    }

    private function fetchRentEvents($user, $fetchInfo)
    {
        if ($user->isAdmin()) {
            $events = Rent::query()
                ->whereNotIn('status', ['for-checkout', 'to-review'])
                ->where('delivery', '>=', $fetchInfo['start'])
                ->where('return', '<=', $fetchInfo['end'])
                ->get();
        } else {
            $events = Rent::query()
                ->where('user_id', $user->id)
                ->where(function ($query) {
                    $query->whereNotIn('status', ['for-checkout', 'to-review'])
                        ->orWhere('user_id', Auth::user()->id);
                })
                ->where('delivery', '>=', $fetchInfo['start'])
                ->where('return', '<=', $fetchInfo['end'])
                ->get();
        }

        return $events->map(
            function (Rent $event) {
                $color = $this->generateColorForRent($event->id);

                return [
                    'title' => $event->status,
                    'start' => $event->delivery,
                    'end' => $event->return,
                    'url' => RentResource::getUrl(name: 'view', parameters: ['record' => $event]),
                    'shouldOpenUrlInNewTab' => true,
                    'color' => $color,
                ];
            }
        )->all();
    }

    private function fetchRentPackageEvents($user, $fetchInfo)
    {
        if ($user->isAdmin()) {
            $events = RentPackage::query()
                ->whereNotIn('status', ['for-checkout', 'to-review'])
                ->where('delivery', '>=', $fetchInfo['start'])
                ->where('return', '<=', $fetchInfo['end'])
                ->get();
        } else {
            $events = RentPackage::query()
                ->where('user_id', $user->id)
                ->where(function ($query) {
                    $query->whereNotIn('status', ['for-checkout', 'to-review'])
                        ->orWhere('user_id', Auth::user()->id);
                })
                ->where('delivery', '>=', $fetchInfo['start'])
                ->where('return', '<=', $fetchInfo['end'])
                ->get();
        }

        return $events->map(
            function (RentPackage $event) {
                $color = $this->generateColorForRentPackage($event->id);

                return [
                    'title' => $event->status,
                    'start' => $event->delivery,
                    'end' => $event->return,
                    'url' => RentPackageResource::getUrl(name: 'view', parameters: ['record' => $event]),
                    'shouldOpenUrlInNewTab' => true,
                    'color' => $color,
                ];
            }
        )->all();
    }

    private function generateColorForRent($rentId): string
    {
        $rent = Rent::find($rentId);

        switch ($rent->status) {
            case 'pending':
                $color = '#ff6400';
                break;
            case 'approved':
                $color = '#FF9800';
                break;
            case 'rejected':
                $color = '#F4511E';
                break;
            case 'completed':
                $color = '#00cc00';
                break;
            case 'for-delivery':
                $color = '#1976D2';
                break;
            case 'for-return':
                $color = '#26C6DA';
                break;
            case 'to-review':
                $color = '#AB47BC';
                break;
            case 'cancelled':
                $color = '#E53935';
                break;
        }

        return $color;
    }

    private function generateColorForRentPackage($rentpackageId): string
    {
        $rent = RentPackage::find($rentpackageId);

        switch ($rent->status) {
            case 'pending':
                $color = '#ff6400';
                break;
            case 'approved':
                $color = '#FF9800';
                break;
            case 'rejected':
                $color = '#F4511E';
                break;
            case 'completed':
                $color = '#00cc00';
                break;
            case 'for-delivery':
                $color = '#1976D2';
                break;
            case 'for-return':
                $color = '#26C6DA';
                break;
            case 'to-review':
                $color = '#AB47BC';
                break;
            case 'cancelled':
                $color = '#E53935';
                break;
        }

        return $color;
    }

    public static function canView(): bool
    {
        return false;
    }
}
