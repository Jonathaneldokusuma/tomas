<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master migration — creates ALL custom Tomas tables from scratch.
 * Safe to run on a fresh MySQL database (no prior SQL import needed).
 * Also safe to re-run: all creates are wrapped in hasTable() checks.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── user ──────────────────────────────────────────────────────────────
        if (! Schema::hasTable('user')) {
            Schema::create('user', function (Blueprint $table) {
                $table->increments('id_user');
                $table->string('nama', 100)->nullable();
                $table->string('no_hp', 15);
                $table->string('alamat', 255)->nullable();
                $table->string('password');
            });
        }

        // ── layanan ───────────────────────────────────────────────────────────
        if (! Schema::hasTable('layanan')) {
            Schema::create('layanan', function (Blueprint $table) {
                $table->increments('id_layanan');
                $table->string('nama_layanan', 100)->nullable();
            });
        }

        // ── tukang ────────────────────────────────────────────────────────────
        if (! Schema::hasTable('tukang')) {
            Schema::create('tukang', function (Blueprint $table) {
                $table->increments('id_tukang');
                $table->string('nama', 100)->nullable();
                $table->string('kategori', 100)->nullable();
                $table->string('lokasi', 200)->nullable();
                $table->text('bio')->nullable();
                $table->tinyInteger('status_aktif')->default(1);
                $table->decimal('tarif', 15, 2)->default(100000);
                $table->string('foto')->nullable();
                $table->timestamps();
            });
        }

        // ── order ─────────────────────────────────────────────────────────────
        if (! Schema::hasTable('order')) {
            Schema::create('order', function (Blueprint $table) {
                $table->increments('id_order');
                $table->unsignedInteger('id_user')->nullable();
                $table->unsignedInteger('id_tukang')->nullable();
                $table->unsignedInteger('id_layanan')->nullable();
                $table->string('alamat', 255)->nullable();
                $table->date('tanggal_kerja')->nullable();
                $table->string('jam_mulai', 10)->nullable();
                $table->string('durasi', 50)->nullable();
                $table->text('deskripsi')->nullable();
                $table->string('metode_bayar', 50)->nullable()->default('Tunai');
                $table->string('status', 30)->nullable()->default('pending');
                $table->timestamps();

                $table->foreign('id_user')->references('id_user')->on('user')->nullOnDelete();
                $table->foreign('id_tukang')->references('id_tukang')->on('tukang')->nullOnDelete();
                $table->foreign('id_layanan')->references('id_layanan')->on('layanan')->nullOnDelete();
            });
        }

        // ── review ────────────────────────────────────────────────────────────
        if (! Schema::hasTable('review')) {
            Schema::create('review', function (Blueprint $table) {
                $table->increments('id_review');
                $table->unsignedInteger('id_order');
                $table->tinyInteger('rating')->default(5);
                $table->text('komentar')->nullable();

                $table->foreign('id_order')->references('id_order')->on('order')->cascadeOnDelete();
            });
        }

        // ── chat ──────────────────────────────────────────────────────────────
        if (! Schema::hasTable('chat')) {
            Schema::create('chat', function (Blueprint $table) {
                $table->increments('id_chat');
                $table->unsignedInteger('id_user');
                $table->unsignedInteger('id_tukang');
                $table->text('pesan');
                $table->boolean('dari_user')->default(true);
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('id_user')->references('id_user')->on('user')->cascadeOnDelete();
                $table->foreign('id_tukang')->references('id_tukang')->on('tukang')->cascadeOnDelete();
            });
        }

        // ── favorit ───────────────────────────────────────────────────────────
        if (! Schema::hasTable('favorit')) {
            Schema::create('favorit', function (Blueprint $table) {
                $table->increments('id_favorit');
                $table->unsignedInteger('id_user');
                $table->unsignedInteger('id_tukang');
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['id_user', 'id_tukang']);
                $table->foreign('id_user')->references('id_user')->on('user')->cascadeOnDelete();
                $table->foreign('id_tukang')->references('id_tukang')->on('tukang')->cascadeOnDelete();
            });
        }

        // ── notifikasi ────────────────────────────────────────────────────────
        if (! Schema::hasTable('notifikasi')) {
            Schema::create('notifikasi', function (Blueprint $table) {
                $table->increments('id_notif');
                $table->unsignedInteger('id_user');
                $table->string('judul', 200);
                $table->text('pesan');
                $table->string('tipe', 50)->default('info');
                $table->boolean('dibaca')->default(false);
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('id_user')->references('id_user')->on('user')->cascadeOnDelete();
            });
        }

        // ── pembayaran ────────────────────────────────────────────────────────
        if (! Schema::hasTable('pembayaran')) {
            Schema::create('pembayaran', function (Blueprint $table) {
                $table->increments('id_pembayaran');
                $table->unsignedInteger('id_order');
                $table->decimal('jumlah', 15, 2);
                $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
                $table->string('snap_token')->nullable();
                $table->string('snap_url')->nullable();
                $table->string('payment_type')->nullable();
                $table->string('transaction_id')->nullable();
                $table->timestamps();

                $table->foreign('id_order')->references('id_order')->on('order')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Drop in reverse foreign-key order
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('favorit');
        Schema::dropIfExists('chat');
        Schema::dropIfExists('review');
        Schema::dropIfExists('order');
        Schema::dropIfExists('tukang');
        Schema::dropIfExists('layanan');
        Schema::dropIfExists('user');
    }
};
