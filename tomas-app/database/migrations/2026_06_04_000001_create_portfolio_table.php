<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('portfolio')) {
            Schema::create('portfolio', function (Blueprint $table) {
                $table->increments('id_portfolio');
                $table->unsignedInteger('id_tukang');
                $table->string('judul', 150)->nullable();
                $table->text('deskripsi')->nullable();
                $table->string('media_path', 255);
                $table->string('media_type', 20)->default('image');
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
        Schema::dropIfExists('portfolio');
    }
};
