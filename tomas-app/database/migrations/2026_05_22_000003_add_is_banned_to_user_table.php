<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('user', 'is_banned')) {
            Schema::table('user', function (Blueprint $table) {
                $table->tinyInteger('is_banned')->default(0)->after('password');
            });
        }
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('is_banned');
        });
    }
};
