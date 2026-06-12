<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivity extends Model
{
    protected $table = 'admin_activities';

    protected $fillable = [
        'admin_username',
        'action',
        'subject_type',
        'subject_id',
        'subject_name',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];
}
