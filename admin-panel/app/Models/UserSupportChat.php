<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSupportChat extends Model
{
    protected $table = 'user_support_chat';
    protected $primaryKey = 'id_user_support_chat';

    protected $fillable = [
        'id_user',
        'kategori',
        'pesan',
        'dari_user',
    ];

    protected $casts = [
        'dari_user' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
