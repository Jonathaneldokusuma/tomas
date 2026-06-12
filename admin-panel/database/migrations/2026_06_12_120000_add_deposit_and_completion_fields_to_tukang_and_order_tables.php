<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tukang')) {
            Schema::table('tukang', function (Blueprint $table) {
                if (! Schema::hasColumn('tukang', 'deposit_balance')) {
                    $table->decimal('deposit_balance', 15, 2)->default(0)->after('tarif');
                }

                if (! Schema::hasColumn('tukang', 'deposit_minimum')) {
                    $table->decimal('deposit_minimum', 15, 2)->default(100000)->after('deposit_balance');
                }
            });
        }

        if (Schema::hasTable('order')) {
            Schema::table('order', function (Blueprint $table) {
                if (! Schema::hasColumn('order', 'difficulty_level')) {
                    $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium')->after('status_payment');
                }

                if (! Schema::hasColumn('order', 'deposit_fee')) {
                    $table->decimal('deposit_fee', 15, 2)->default(0)->after('difficulty_level');
                }

                if (! Schema::hasColumn('order', 'user_completed_at')) {
                    $table->timestamp('user_completed_at')->nullable()->after('deposit_fee');
                }

                if (! Schema::hasColumn('order', 'tukang_completed_at')) {
                    $table->timestamp('tukang_completed_at')->nullable()->after('user_completed_at');
                }

                if (! Schema::hasColumn('order', 'deposit_deducted_at')) {
                    $table->timestamp('deposit_deducted_at')->nullable()->after('tukang_completed_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order')) {
            Schema::table('order', function (Blueprint $table) {
                $drop = [];
                foreach (['difficulty_level', 'deposit_fee', 'user_completed_at', 'tukang_completed_at', 'deposit_deducted_at'] as $column) {
                    if (Schema::hasColumn('order', $column)) {
                        $drop[] = $column;
                    }
                }
                if ($drop) {
                    $table->dropColumn($drop);
                }
            });
        }

        if (Schema::hasTable('tukang')) {
            Schema::table('tukang', function (Blueprint $table) {
                $drop = [];
                foreach (['deposit_balance', 'deposit_minimum'] as $column) {
                    if (Schema::hasColumn('tukang', $column)) {
                        $drop[] = $column;
                    }
                }
                if ($drop) {
                    $table->dropColumn($drop);
                }
            });
        }
    }
};
