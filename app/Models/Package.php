<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'equipment_id',
        'description',
        'image',
        'price',
        'status',
        
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function items()
    {
        return $this->hasMany(PackageEquipment::class);
    }

    protected static function booted()
    {
        static::saving(function ($package) {
            $package->calculateAndSetSubtotal();
        });
    }

    public function calculateAndSetSubtotal()
    {
        // Calculate and set the subtotal based on the items
        $price = $this->items->reduce(function ($carry, $item) {
            return $carry + ($item->unit_price * $item->quantity);
        }, 0);

        // Update the model with the new subtotal
        $this->price = number_format($price, 2, '.', '');
    }
}
