<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_activities')) {
            Schema::create('admin_activities', function (Blueprint $table) {
                $table->increments('id_admin_activity');
                $table->string('admin_username', 100)->nullable();
                $table->string('action', 80);
                $table->string('subject_type', 50)->nullable();
                $table->unsignedInteger('subject_id')->nullable();
                $table->string('subject_name', 150)->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activities');
    }
};
