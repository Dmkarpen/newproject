<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Создание нового заказа (store).
     * Ожидаем, что фронтенд пошлёт:
     *  - name, phone, address (string)
     *  - items: array of { id, title, price, count }
     *  - total: string (или число)
     */
    public function store(Request $request)
    {
        // Валидируем
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'items' => 'required|array',
            'items.*.id' => 'nullable|integer', // product_id (может быть null)
            'items.*.title' => 'required|string|max:255',
            'items.*.price' => 'required|numeric',
            'items.*.count' => 'required|integer|min:1',
            'total' => 'required|numeric',
        ]);

        // Создаём запись в таблице orders
        $order = Order::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'total' => $data['total'],
        ]);

        // Сохраняем товары
        foreach ($data['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'] ?? null, // если передаёте id
                'title' => $item['title'],
                'price' => $item['price'],
                'count' => $item['count'],
            ]);
        }

        // Возвращаем JSON-ответ
        return response()->json([
            'message' => 'Order created successfully',
            'order_id' => $order->id,
        ], 201);
    }

    /**
     * Пример: получить список всех заказов
     */
    public function index()
    {
        // Eager-load items
        $orders = Order::with('items')->get();
        return response()->json($orders);
    }

    /**
     * Пример: получить конкретный заказ
     */
    public function show($id)
    {
        $order = Order::with('items')->findOrFail($id);
        return response()->json($order);
    }
}
