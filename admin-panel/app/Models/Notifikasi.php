<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table      = 'notifikasi';
    protected $primaryKey = 'id_notif';
    public    $timestamps = false;

    protected $fillable = ['id_user', 'judul', 'pesan', 'tipe', 'dibaca', 'created_at'];
    protected $casts    = ['created_at' => 'datetime', 'dibaca' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /** Helper: buat notif baru */
    public static function kirim($userId, $judul, $pesan, $tipe = 'info')
    {
        static::create([
            'id_user'    => $userId,
            'judul'      => $judul,
            'pesan'      => $pesan,
            'tipe'       => $tipe,
            'dibaca'     => 0,
            'created_at' => now(),
        ]);
    }
}
