<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rent_number',
        'user_id',
        'equipment_id',
        'address',
        'contact',
        'quantity',
        'unit_price',
        'total_price',
        'date_of_pickup',
        'date_of_delivery',
        'status',
        'type',
        'delivery_fee',
        'rating',
        'comment',
        'image',

    ];

    protected $casts = [
        'image' => 'array',
    ];

    public static function boot()
    {
    parent::boot();

    static::saving(function ($rent) {
        $quantity = (float) $rent->quantity;
        $unitPrice = (float) $rent->unit_price;

        if ($rent->isDirty(['quantity', 'unit_price'])) {
            if (!is_null($quantity) && !is_null($unitPrice)) {
                $rent->total_price = $quantity * $unitPrice;

                // Deduct the rented quantity from the equipment's inventory
                if ($equipment = $rent->equipment) {
                    $currentInventory = (float) $equipment->quantity;

                    // Ensure the current inventory is greater than or equal to the rented quantity
                    if ($currentInventory >= $quantity) {
                        $equipment->quantity = $currentInventory - $quantity;
                        $equipment->save();
                    } else {
                        // Handle insufficient inventory error as needed
                        // You may throw an exception, log an error, or take appropriate action
                    }
                }
            }
        }
    });
    
    static::updating(function ($rent) {
        $statusChangedToCancelled = $rent->isDirty('status') && $rent->status === 'cancelled';
        $statusChangedToCompleted = $rent->isDirty('status') && $rent->status === 'completed';

        if ($statusChangedToCancelled || $statusChangedToCompleted) {
            $quantity = (float) $rent->quantity;

            // Add the rented quantity back to the equipment's inventory
            if ($equipment = $rent->equipment) {
                $currentInventory = (float) $equipment->quantity;
                $equipment->quantity = $currentInventory + $quantity;
                $equipment->save();
            }
        }
    });
}

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
    
}
