<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewedProduct extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'product_id', 'viewed_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
