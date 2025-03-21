<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    // Если у вас нет полей "timestamps", но мы их добавили, значит оставим
    // protected $timestamps = false; // не нужно, т.к. timestamps есть

    // Разрешаем массовое заполнение (если нужно)
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
    ];
}
