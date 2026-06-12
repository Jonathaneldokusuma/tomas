<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('support_chat')) {
            Schema::create('support_chat', function (Blueprint $table) {
                $table->increments('id_support_chat');
                $table->unsignedInteger('id_tukang');
                $table->string('kategori', 50)->default('bantuan');
                $table->text('pesan');
                $table->boolean('dari_tukang')->default(true);
                $table->timestamps();

                $table->foreign('id_tukang')
                    ->references('id_tukang')
                    ->on('tukang')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_chat');
    }
};
