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

    protected $fillable = ['nama', 'no_hp', 'alamat', 'password', 'is_banned'];
    protected $hidden   = ['password'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'id_user', 'id_user');
    }

    public function badges()
    {
        return $this->hasMany(BadgeAward::class, 'target_id', 'id_user')
            ->where('target_type', 'user');
    }

    public function supportChats()
    {
        return $this->hasMany(UserSupportChat::class, 'id_user', 'id_user');
    }
}
