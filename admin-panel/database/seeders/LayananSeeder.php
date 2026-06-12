<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nama_layanan' => 'Servis AC'],
            ['nama_layanan' => 'Instalasi Listrik'],
            ['nama_layanan' => 'Perbaikan Pipa & Plumbing'],
            ['nama_layanan' => 'Pengecatan Rumah'],
            ['nama_layanan' => 'Perbaikan Atap'],
            ['nama_layanan' => 'Bersih-Bersih Rumah'],
            ['nama_layanan' => 'Perbaikan Pintu & Jendela'],
            ['nama_layanan' => 'Taman & Lanskap'],
        ];

        foreach ($items as $item) {
            DB::table('layanan')->insertOrIgnore($item);
        }
    }
}
