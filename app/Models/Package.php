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
        'subtotal',
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
    
}
