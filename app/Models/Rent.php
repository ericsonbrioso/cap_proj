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

    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($rent) {
        
            $quantity = (float) $rent->quantity;
            $unitPrice = (float) $rent->unit_price;

            if (!is_null($quantity) && !is_null($unitPrice)) {
                $rent->total_price = $quantity * $unitPrice;
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
