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
        'rentpackage_id',
        'code',
        'description',
        'image',
        'total',
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

        // Calculate total based on the selected items and quantities
        $total = $selectedItems->reduce(function ($total, $item) use ($prices) {
            return $total + ($prices[$item['equipment_id']] * $item['quantity']);
        }, 0);

        // Set the total attribute
        $this->setAttribute('total', $total);
    }
}
