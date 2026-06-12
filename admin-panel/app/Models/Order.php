<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'order';
    protected $primaryKey = 'id_order';


    protected $fillable = [
        'id_user', 'id_tukang', 'id_layanan',
        'alamat', 'latitude', 'longitude', 'tanggal_kerja', 'jam_mulai', 'durasi',
        'deskripsi', 'catatan_tukang', 'metode_bayar', 'status',
        'bukti_bayar', 'status_payment',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function tukang()
    {
        return $this->belongsTo(Tukang::class, 'id_tukang', 'id_tukang');
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan', 'id_layanan');
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'id_order', 'id_order');
    }
}
