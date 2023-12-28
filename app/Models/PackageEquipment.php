<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageEquipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'equipment_id',
        'unit_price',
        'quantity',
        
    ];
    
    public function package()
    {   
        return $this->belongsTo(Package::class);
    }  

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($packageEquipment) {
            $equipment = Equipment::find($packageEquipment->equipment_id);

            if ($equipment) {
                $equipment->decrement('quantity', $packageEquipment->quantity);
            }
        });

        static::deleted(function ($packageEquipment) {
            $equipment = Equipment::find($packageEquipment->equipment_id);

            if ($equipment) {
                $equipment->increment('quantity', $packageEquipment->quantity);
            }
        });
    }
}
