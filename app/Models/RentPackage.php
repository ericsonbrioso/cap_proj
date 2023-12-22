<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'rent_number',
        'user_id',
        'package_id',
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
    });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
