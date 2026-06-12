<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table      = 'chat';
    protected $primaryKey = 'id_chat';
    public    $timestamps = false;

    protected $fillable = ['id_user', 'id_tukang', 'pesan', 'dari_user', 'created_at'];

    protected $casts = ['created_at' => 'datetime', 'dari_user' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function tukang()
    {
        return $this->belongsTo(Tukang::class, 'id_tukang', 'id_tukang');
    }
}
