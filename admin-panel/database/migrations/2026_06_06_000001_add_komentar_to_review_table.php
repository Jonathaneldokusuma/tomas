<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('review') || Schema::hasColumn('review', 'komentar')) {
            return;
        }

        Schema::table('review', function (Blueprint $table) {
            $table->text('komentar')->nullable()->after('rating');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('review') || ! Schema::hasColumn('review', 'komentar')) {
            return;
        }

        Schema::table('review', function (Blueprint $table) {
            $table->dropColumn('komentar');
        });
    }
};
