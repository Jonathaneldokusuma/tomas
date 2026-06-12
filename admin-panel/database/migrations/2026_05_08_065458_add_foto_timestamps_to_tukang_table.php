<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFotoTimestampsToTukangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tukang', function (Blueprint $table) {
            if (! Schema::hasColumn('tukang', 'foto')) {
                if (Schema::hasColumn('tukang', 'bio')) {
                    $table->string('foto')->nullable()->after('bio');
                } else {
                    $table->string('foto')->nullable();
                }
            }
            if (! Schema::hasColumn('tukang', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down()
    {
        Schema::table('tukang', function (Blueprint $table) {
            if (Schema::hasColumn('tukang', 'foto')) {
                $table->dropColumn('foto');
            }
            $cols = [];
            if (Schema::hasColumn('tukang', 'created_at')) {
                $cols[] = 'created_at';
            }
            if (Schema::hasColumn('tukang', 'updated_at')) {
                $cols[] = 'updated_at';
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
}
