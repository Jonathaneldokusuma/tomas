<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'nama'     => 'Morris Test',
                'no_hp'    => '08123456789',
                'alamat'   => 'Jl. Merdeka No. 1, Jakarta Pusat',
                'password' => Hash::make('password123'),
            ],
            [
                'nama'     => 'Siti Rahma',
                'no_hp'    => '08567890123',
                'alamat'   => 'Jl. Pahlawan No. 5, Bandung',
                'password' => Hash::make('password123'),
            ],
        ];

        foreach ($items as $item) {
            // skip if no_hp already exists
            if (! DB::table('user')->where('no_hp', $item['no_hp'])->exists()) {
                DB::table('user')->insert($item);
            }
        }
    }
}
