<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportChat extends Model
{
    protected $table = 'support_chat';
    protected $primaryKey = 'id_support_chat';

    protected $fillable = [
        'id_tukang',
        'kategori',
        'pesan',
        'dari_tukang',
    ];

    protected $casts = [
        'dari_tukang' => 'boolean',
    ];

    public function tukang()
    {
        return $this->belongsTo(Tukang::class, 'id_tukang', 'id_tukang');
    }
}
