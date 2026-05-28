<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastMessage extends Model
{
    protected $table = 'broadcast_messages';
    protected $primaryKey = 'id_broadcast';

    protected $fillable = ['judul', 'isi', 'tipe'];
}
