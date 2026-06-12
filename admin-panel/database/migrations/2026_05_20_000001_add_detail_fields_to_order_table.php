<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('order', 'alamat')) {
            return; // already created by master migration
        }
        Schema::table('order', function (Blueprint $table) {
            $table->string('alamat', 255)->nullable()->after('id_layanan');
            $table->date('tanggal_kerja')->nullable()->after('alamat');
            $table->string('jam_mulai', 10)->nullable()->after('tanggal_kerja');
            $table->string('durasi', 50)->nullable()->after('jam_mulai');
            $table->text('deskripsi')->nullable()->after('durasi');
            $table->string('metode_bayar', 50)->nullable()->default('Tunai')->after('deskripsi');
            $table->string('status', 30)->nullable()->default('pending')->after('metode_bayar');
            $table->timestamps();
        });
        // end column-existence check
    }

    public function down(): void
    {
        Schema::table('order', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'tanggal_kerja', 'jam_mulai', 'durasi', 'deskripsi', 'metode_bayar', 'status', 'created_at', 'updated_at']);
        });
    }
};
