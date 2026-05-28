<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order', function (Blueprint $table) {
            if (!Schema::hasColumn('order', 'bukti_bayar')) {
                $table->string('bukti_bayar')->nullable()->after('metode_bayar');
            }
            if (!Schema::hasColumn('order', 'status_payment')) {
                $table->enum('status_payment', ['pending', 'uploaded', 'confirmed'])->default('pending')->after('bukti_bayar');
            }
            if (!Schema::hasColumn('order', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('order', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('order', 'catatan_tukang')) {
                $table->text('catatan_tukang')->nullable()->after('deskripsi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order', function (Blueprint $table) {
            $table->dropColumn(['bukti_bayar', 'status_payment', 'latitude', 'longitude', 'catatan_tukang']);
        });
    }
};
