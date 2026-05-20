<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'user';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = ['nama', 'no_hp', 'alamat', 'password'];
    protected $hidden   = ['password'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'id_user', 'id_user');
    }
}
