<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TukangSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'nama'        => 'Budi Santoso',
                'kategori'    => 'Servis AC',
                'lokasi'      => 'Jakarta Selatan',
                'bio'         => 'Teknisi AC berpengalaman 8 tahun. Menangani semua merk AC.',
                'status_aktif'=> 1,
                'status_verifikasi' => 'verified',
                'tarif'       => 150000,
                'foto'        => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama'        => 'Ahmad Fauzi',
                'kategori'    => 'Instalasi Listrik',
                'lokasi'      => 'Jakarta Timur',
                'bio'         => 'Listrikawan bersertifikat PLN. Spesialis instalasi rumah & gedung.',
                'status_aktif'=> 1,
                'status_verifikasi' => 'verified',
                'tarif'       => 120000,
                'foto'        => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama'        => 'Slamet Riyadi',
                'kategori'    => 'Perbaikan Pipa & Plumbing',
                'lokasi'      => 'Bekasi',
                'bio'         => 'Tukang ledeng profesional. Perbaikan bocor, saluran mampet, dll.',
                'status_aktif'=> 1,
                'status_verifikasi' => 'verified',
                'tarif'       => 100000,
                'foto'        => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama'        => 'Hendra Wijaya',
                'kategori'    => 'Pengecatan Rumah',
                'lokasi'      => 'Tangerang',
                'bio'         => 'Spesialis pengecatan interior & eksterior. Hasil rapi dan tahan lama.',
                'status_aktif'=> 1,
                'status_verifikasi' => 'verified',
                'tarif'       => 200000,
                'foto'        => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama'        => 'Deni Kurniawan',
                'kategori'    => 'Bersih-Bersih Rumah',
                'lokasi'      => 'Depok',
                'bio'         => 'Jasa bersih-bersih rumah, kost, dan kantor. Cepat dan teliti.',
                'status_aktif'=> 1,
                'status_verifikasi' => 'verified',
                'tarif'       => 80000,
                'foto'        => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama'        => 'Rian Prabowo',
                'kategori'    => 'Perbaikan Atap',
                'lokasi'      => 'Bogor',
                'bio'         => 'Ahli perbaikan atap bocor, genteng, dan plafon. Garansi pekerjaan.',
                'status_aktif'=> 1,
                'status_verifikasi' => 'verified',
                'tarif'       => 175000,
                'foto'        => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        foreach ($items as $item) {
            DB::table('tukang')->insertOrIgnore($item);
        }
    }
}
