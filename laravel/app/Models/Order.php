<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Если хотите массовое заполнение (fillable)
    protected $fillable = [
        'name',
        'phone',
        'address',
        'total',
        'status',
        'delivery_type',
        'np_city_ref',
        'np_warehouse_ref',
    ];

    // Связь "один ко многим" с OrderItem
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
