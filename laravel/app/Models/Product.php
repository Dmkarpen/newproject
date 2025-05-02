<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'price',
        'thumbnail',
        'warrantyInformation',
        'stock'
    ];

    // Додаємо доступ до віртуального поля
    protected $appends = ['available'];

    // Метод для завантаження пов’язаних зображень
    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'product_id', 'id');
    }

    // Метод для відгуків
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'product_id', 'id');
    }

    // Аксесор для віртуального поля "available"
    public function getAvailableAttribute(): bool
    {
        return $this->stock > 0;
    }
}
