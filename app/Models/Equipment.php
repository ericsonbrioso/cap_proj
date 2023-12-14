<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type_id',
        'description',
        'image',
        'price',
        'condition', 
        'status',
        'quantity',
        
    ];

    public function type()
    {
        return $this->belongsTo(Type::class);
    }
    
}
