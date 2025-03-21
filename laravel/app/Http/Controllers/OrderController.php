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

    public function update(Request $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);

        // Валидируем поля заказа + items
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:50',
            'address' => 'sometimes|string|max:500',
            'total' => 'sometimes|numeric',
            'status' => 'sometimes|string|max:50',
            'items' => 'sometimes|array',

            // Для существующего item: id != null => обновляем
            // Для нового item: id = null => создаём
            'items.*.id' => 'nullable|integer',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.count' => 'required|integer|min:1',
        ]);

        // Обновляем поля заказа
        $order->fill($data);
        $order->save();

        // Если есть items, обрабатываем
        if (isset($data['items'])) {
            foreach ($data['items'] as $itemData) {
                if (!empty($itemData['id'])) {
                    // Обновляем существующий item
                    $item = \App\Models\OrderItem::where('order_id', $order->id)
                        ->find($itemData['id']);
                    if ($item) {
                        // Находим product, чтобы вдруг обновить price/title, если нужно
                        // Но обычно при обновлении existing item
                        // может не менять product_id.
                        // В зависимости от вашей логики
                        if ($itemData['product_id'] != $item->product_id) {
                            $product = \App\Models\Product::findOrFail($itemData['product_id']);
                            $item->product_id = $product->id;
                            $item->title = $product->title;
                            $item->price = $product->price;
                        }
                        $item->count = $itemData['count'];
                        $item->save();
                    }
                } else {
                    // Новый item (id=null)
                    // Найдём product
                    $product = \App\Models\Product::findOrFail($itemData['product_id']);

                    \App\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'title' => $product->title,
                        'price' => $product->price,
                        'count' => $itemData['count'],
                    ]);
                }
            }
        }

        // Пересчитываем total
        $order->refresh();
        $sum = 0;
        foreach ($order->items as $itm) {
            $sum += $itm->price * $itm->count;
        }
        $order->total = $sum;
        $order->save();

        return response()->json([
            'message' => 'Order updated successfully',
            'order'   => $order->fresh('items'),
        ]);
    }

    // удалять конкретный товар по отдельному запросу DELETE
    public function removeItem($orderId, $itemId)
    {
        $order = Order::findOrFail($orderId);
        // Ищем OrderItem, принадлежащий этому заказу
        $item = \App\Models\OrderItem::where('order_id', $order->id)
            ->findOrFail($itemId);

        $item->delete();

        // Пересчитываем total
        $order->load('items'); // перезагрузим связь
        $sum = 0;
        foreach ($order->items as $itm) {
            $sum += $itm->price * $itm->count;
        }
        $order->total = $sum;
        $order->save();

        return response()->json([
            'message' => 'Item removed successfully',
            'order'   => $order->fresh('items'),
        ]);
    }

    /**
     * Удалить заказ (для админки).
     * DELETE /api/orders/{id}
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        // Можно сначала удалить связанные OrderItem, если в БД нет каскадного удаления
        // OrderItem::where('order_id', $order->id)->delete();

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully'
        ]);
    }
}
