<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tukang extends Model
{
    protected $table = 'tukang';
    protected $primaryKey = 'id_tukang';
    public $timestamps = true;

    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'nama', 'status_aktif', 'lokasi', 'alamat', 'kategori', 'bio', 'foto', 'tarif',
        'username', 'password', 'no_hp', 'no_ktp', 'foto_ktp', 'foto_selfie',
        'status_verifikasi', 'latitude', 'longitude', 'deposit_balance', 'deposit_minimum',
    ];

    protected $casts = [
        'deposit_balance' => 'decimal:2',
        'deposit_minimum' => 'decimal:2',
        'status_aktif' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'id_tukang', 'id_tukang');
    }

    public function reviews()
    {
        return $this->hasManyThrough(Review::class, Order::class, 'id_tukang', 'id_order', 'id_tukang', 'id_order');
    }

    public function favorit()
    {
        return $this->hasMany(Favorit::class, 'id_tukang', 'id_tukang');
    }

    public function portfolios()
    {
        return $this->hasMany(Portfolio::class, 'id_tukang', 'id_tukang');
    }

    public function badges()
    {
        return $this->hasMany(BadgeAward::class, 'target_id', 'id_tukang')
            ->where('target_type', 'tukang');
    }

    public function supportChats()
    {
        return $this->hasMany(SupportChat::class, 'id_tukang', 'id_tukang');
    }

    public function isFavoritedBy($userId)
    {
        return $this->favorit()->where('id_user', $userId)->exists();
    }
}
