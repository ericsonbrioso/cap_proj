<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'rent_id',
        'package_id',
        'unit_price',
        'quantity',
        
    ];
    
    public function rent()
    {
        return $this->belongsTo(Rent::class);
    }
}
