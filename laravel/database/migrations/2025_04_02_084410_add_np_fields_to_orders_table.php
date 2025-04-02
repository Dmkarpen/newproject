<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('np_city_ref')->nullable();
            $table->string('np_warehouse_ref')->nullable();
            $table->string('delivery_type')->default('pickup'); // pickup або courier
            $table->text('address')->nullable()->change(); // зробити address необов’язковим
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['np_city_ref', 'np_warehouse_ref', 'delivery_type']);
            $table->text('address')->nullable(false)->change(); // повернути як було
        });
    }
};
