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
}
