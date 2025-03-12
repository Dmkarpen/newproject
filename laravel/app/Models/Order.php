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
    ];

    // Связь "один ко многим" с OrderItem
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
