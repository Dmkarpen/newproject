<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'title',
        'price',
        'count',
    ];

    // Связь "многие к одному" с Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
