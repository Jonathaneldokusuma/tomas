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
                $table->string('foto')->nullable()->after('bio');
            }
            if (! Schema::hasColumn('tukang', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down()
    {
        Schema::table('tukang', function (Blueprint $table) {
            $table->dropColumn(['foto', 'created_at', 'updated_at']);
        });
    }
}
