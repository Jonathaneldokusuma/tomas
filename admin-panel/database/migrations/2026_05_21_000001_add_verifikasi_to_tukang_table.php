<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tukang', function (Blueprint $table) {
            $table->string('username', 50)->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('foto_ktp')->nullable();
            $table->string('foto_selfie')->nullable();
            $table->enum('status_verifikasi', ['pending', 'verified', 'rejected'])->default('pending');
        });
    }

    public function down(): void
    {
        Schema::table('tukang', function (Blueprint $table) {
            $table->dropColumn(['username', 'password', 'no_hp', 'foto_ktp', 'foto_selfie', 'status_verifikasi']);
        });
    }
};
