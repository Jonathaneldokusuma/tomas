<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_support_chat')) {
            Schema::create('user_support_chat', function (Blueprint $table) {
                $table->increments('id_user_support_chat');
                $table->unsignedInteger('id_user');
                $table->string('kategori', 50)->default('bantuan');
                $table->text('pesan');
                $table->boolean('dari_user')->default(true);
                $table->timestamps();

                $table->foreign('id_user')
                    ->references('id_user')
                    ->on('user')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_support_chat');
    }
};
