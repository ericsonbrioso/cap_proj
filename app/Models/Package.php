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
        'code',
        'description',
        'image',
        'total',
        'add-ons',
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

    public function rentpackage()
    {
        return $this->hasMany(RentPackage::class);
    }

    public function averageRating()
    {
    return $this->rent->avg('rating');
    }
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($package) {
            $package->calculateAndSetTotal();
        });

        static::updating(function ($package) {
            $package->calculateAndSetTotal();
        });
    }

    public function calculateAndSetTotal()
{
    // Retrieve all selected items and remove empty rows
    $selectedItems = $this->items->filter(function ($item) {
        return !empty($item['equipment_id']) && !empty($item['quantity']);
    });

    // Retrieve prices for all selected items
    $prices = Equipment::find($selectedItems->pluck('equipment_id'))->pluck('price', 'id');

    // Calculate subtotal based on the selected items and quantities
    $subtotal = $selectedItems->reduce(function ($subtotal, $item) use ($prices) {
        return $subtotal + ($prices[$item['equipment_id']] * $item['quantity']);
    }, 0);

    // Retrieve add-ons
    $addons = $this->getAttribute('add-ons');

    // Calculate total including add-ons
    $total = $subtotal + $addons;

    // Set the total attribute
    $this->setAttribute('total', number_format($total, 2, '.', ''));
}
    
}
