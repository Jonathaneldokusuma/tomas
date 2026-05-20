<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTarifToTukangAndCreatePembayaranTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add tarif to tukang
        if (! Schema::hasColumn('tukang', 'tarif')) {
            Schema::table('tukang', function (Blueprint $table) {
                $table->decimal('tarif', 15, 2)->default(100000)->after('status_aktif');
            });
        }

        // Create pembayaran table (skip if already created by master migration)
        if (! Schema::hasTable('pembayaran')) {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('id_pembayaran');
            $table->unsignedInteger('id_order');
            $table->decimal('jumlah', 15, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->string('snap_token')->nullable();
            $table->string('snap_url')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });
        } // end if hasTable
    }

    public function down()
    {
        Schema::dropIfExists('pembayaran');
        if (Schema::hasColumn('tukang', 'tarif')) {
            Schema::table('tukang', function (Blueprint $table) {
                $table->dropColumn('tarif');
            });
        }
    }
}
