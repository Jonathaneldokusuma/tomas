<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tukang')
            ->where(function ($query) {
                $query->whereNull('username')
                    ->orWhere('username', '');
            })
            ->where('status_verifikasi', '!=', 'verified')
            ->update([
                'status_verifikasi' => 'verified',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Data migration only: do not downgrade verification state automatically.
    }
};
