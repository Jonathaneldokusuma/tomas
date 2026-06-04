<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $table = 'portfolio';
    protected $primaryKey = 'id_portfolio';

    protected $fillable = [
        'id_tukang',
        'judul',
        'deskripsi',
        'media_path',
        'media_type',
    ];

    public function tukang()
    {
        return $this->belongsTo(Tukang::class, 'id_tukang', 'id_tukang');
    }
}
