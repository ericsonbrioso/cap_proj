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

    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
