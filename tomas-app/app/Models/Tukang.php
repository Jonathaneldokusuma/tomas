<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tukang extends Model
{
    protected $table = 'tukang';
    protected $primaryKey = 'id_tukang';
    public $timestamps = true;

    protected $fillable = ['nama', 'status_aktif', 'lokasi', 'kategori', 'bio', 'foto', 'tarif'];

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

    public function isFavoritedBy($userId)
    {
        return $this->favorit()->where('id_user', $userId)->exists();
    }
}
