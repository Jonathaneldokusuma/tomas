<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only run on MySQL/MariaDB (safe no-op elsewhere)
        try {
            $driver = DB::getDriverName();
        } catch (\Throwable $e) {
            return;
        }

        if (! in_array($driver, ['mysql', 'mysqli', 'pdo_mysql'])) {
            return;
        }

        $statements = [
            "ALTER TABLE `user` MODIFY `id_user` INT NOT NULL AUTO_INCREMENT",
            "ALTER TABLE `layanan` MODIFY `id_layanan` INT NOT NULL AUTO_INCREMENT",
            "ALTER TABLE `tukang` MODIFY `id_tukang` INT NOT NULL AUTO_INCREMENT",
            "ALTER TABLE `order` MODIFY `id_order` INT NOT NULL AUTO_INCREMENT",
            "ALTER TABLE `review` MODIFY `id_review` INT NOT NULL AUTO_INCREMENT",
            "ALTER TABLE `chat` MODIFY `id_chat` INT NOT NULL AUTO_INCREMENT",
            "ALTER TABLE `favorit` MODIFY `id_favorit` INT NOT NULL AUTO_INCREMENT",
            "ALTER TABLE `notifikasi` MODIFY `id_notif` INT NOT NULL AUTO_INCREMENT",
            "ALTER TABLE `pembayaran` MODIFY `id_pembayaran` INT NOT NULL AUTO_INCREMENT",
        ];

        foreach ($statements as $sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
                // ignore failures for tables that don't exist or already have AUTO_INCREMENT
            }
        }
    }

    public function down(): void
    {
        try {
            $driver = DB::getDriverName();
        } catch (\Throwable $e) {
            return;
        }

        if (! in_array($driver, ['mysql', 'mysqli', 'pdo_mysql'])) {
            return;
        }

        $statements = [
            "ALTER TABLE `user` MODIFY `id_user` INT NOT NULL",
            "ALTER TABLE `layanan` MODIFY `id_layanan` INT NOT NULL",
            "ALTER TABLE `tukang` MODIFY `id_tukang` INT NOT NULL",
            "ALTER TABLE `order` MODIFY `id_order` INT NOT NULL",
            "ALTER TABLE `review` MODIFY `id_review` INT NOT NULL",
            "ALTER TABLE `chat` MODIFY `id_chat` INT NOT NULL",
            "ALTER TABLE `favorit` MODIFY `id_favorit` INT NOT NULL",
            "ALTER TABLE `notifikasi` MODIFY `id_notif` INT NOT NULL",
            "ALTER TABLE `pembayaran` MODIFY `id_pembayaran` INT NOT NULL",
        ];

        foreach ($statements as $sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
};
