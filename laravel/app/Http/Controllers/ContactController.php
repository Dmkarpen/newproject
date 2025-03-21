<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Сохранить сообщение из формы "Contact Us".
     */
    public function store(Request $request)
    {
        // Валидируем поля
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Создаём запись в таблице contact_messages
        ContactMessage::create($data);

        // Вернём JSON-ответ или редирект
        return response()->json([
            'message' => 'Contact message stored successfully!',
        ], 201);
    }
}
