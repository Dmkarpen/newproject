<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // Связь с таблицей orders
            $table->unsignedBigInteger('order_id');

            // Связь с таблицей products
            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('title');
            $table->decimal('price', 8, 2);
            $table->integer('count');

            $table->timestamps();

            // Внешний ключ на orders.id
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');

            // Внешний ключ на products.id (при желании)
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
