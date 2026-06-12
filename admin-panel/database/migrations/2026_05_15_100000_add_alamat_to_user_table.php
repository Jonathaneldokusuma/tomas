<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlamatToUserTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('user', 'alamat')) {
            Schema::table('user', function (Blueprint $table) {
                $table->string('alamat', 255)->nullable()->after('no_hp');
            });
        }
    }

    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('alamat');
        });
    }
}
