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
        'address',
        'contact',
        'quantity',
        'date_of_pickup',
        'date_of_delivery',
        'status', 

        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(RentItem::class);
    }

    public function packageitems()
    {
        return $this->hasMany(RentPackage::class);
    }
}
