<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorit extends Model
{
    protected $table      = 'favorit';
    protected $primaryKey = 'id_favorit';
    public    $timestamps = false;

    protected $fillable = ['id_user', 'id_tukang', 'created_at'];
    protected $casts    = ['created_at' => 'datetime'];

    public function tukang()
    {
        return $this->belongsTo(Tukang::class, 'id_tukang', 'id_tukang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
