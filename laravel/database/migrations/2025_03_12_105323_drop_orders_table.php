<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::dropIfExists('orders');
}

public function down()
{
    // Если вдруг откатить — можно заново создать таблицу,
    // но если она вам не нужна, можно оставить пустым
}
};
