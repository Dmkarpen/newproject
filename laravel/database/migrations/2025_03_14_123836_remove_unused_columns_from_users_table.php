<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_verified_at',
                'remember_token',
                'api_token',
            ]);
        });
    }

    public function down(): void
    {
        // Если вдруг нужно откатить миграцию, вернём столбцы
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken(); // эквивалент ->string('remember_token', 100)->nullable();
            $table->string('api_token', 80)->nullable()->unique();
        });
    }
};
