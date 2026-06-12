<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tukang', function (Blueprint $table) {
            if (!Schema::hasColumn('tukang', 'no_ktp'))    $table->string('no_ktp', 20)->nullable()->after('no_hp');
            if (!Schema::hasColumn('tukang', 'alamat'))    $table->text('alamat')->nullable()->after('lokasi');
            if (!Schema::hasColumn('tukang', 'latitude'))  $table->decimal('latitude', 10, 7)->nullable()->after('alamat');
            if (!Schema::hasColumn('tukang', 'longitude')) $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('tukang', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('tukang', 'no_ktp'))    $cols[] = 'no_ktp';
            if (Schema::hasColumn('tukang', 'alamat'))    $cols[] = 'alamat';
            if (Schema::hasColumn('tukang', 'latitude'))  $cols[] = 'latitude';
            if (Schema::hasColumn('tukang', 'longitude')) $cols[] = 'longitude';
            if ($cols) $table->dropColumn($cols);
        });
    }
};
