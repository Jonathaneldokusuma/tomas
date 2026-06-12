<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcast_messages', function (Blueprint $table) {
            $table->id('id_broadcast');
            $table->string('judul', 200);
            $table->text('isi');
            $table->string('tipe', 20)->default('info'); // info, warning, promo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_messages');
    }
};
