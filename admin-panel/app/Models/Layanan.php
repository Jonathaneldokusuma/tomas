<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanan';
    protected $primaryKey = 'id_layanan';
    public $timestamps = false;

    protected $fillable = ['nama_layanan'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'id_layanan', 'id_layanan');
    }
}
