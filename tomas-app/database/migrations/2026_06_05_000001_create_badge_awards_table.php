<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('badge_awards')) {
            Schema::create('badge_awards', function (Blueprint $table) {
                $table->increments('id_badge_award');
                $table->enum('target_type', ['user', 'tukang']);
                $table->unsignedInteger('target_id');
                $table->string('nama', 120);
                $table->text('deskripsi')->nullable();
                $table->string('gambar', 255)->nullable();
                $table->string('warna', 20)->nullable();
                $table->string('created_by_admin', 100)->nullable();
                $table->timestamps();

                $table->index(['target_type', 'target_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_awards');
    }
};
