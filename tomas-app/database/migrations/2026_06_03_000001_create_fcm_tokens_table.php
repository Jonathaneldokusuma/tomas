<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FCM tokens table – stores one token per device per user/tukang
        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('user_type', 10); // 'user' or 'tukang'
            $table->unsignedBigInteger('user_id');
            $table->string('fcm_token', 500);
            $table->string('device_id', 200)->nullable();
            $table->timestamps();

            $table->unique(['user_type', 'user_id', 'device_id'], 'fcm_unique_device');
            $table->index(['user_type', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};
