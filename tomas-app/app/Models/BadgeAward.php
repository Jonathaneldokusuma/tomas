<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadgeAward extends Model
{
    protected $table = 'badge_awards';
    protected $primaryKey = 'id_badge_award';

    protected $fillable = [
        'target_type',
        'target_id',
        'nama',
        'deskripsi',
        'gambar',
        'warna',
        'created_by_admin',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->gambar ? url('storage/' . $this->gambar) : null;
    }

    public function toPayload(): array
    {
        return [
            'id_badge'    => $this->id_badge_award,
            'key'         => 'custom_' . $this->id_badge_award,
            'label'       => $this->nama,
            'description' => $this->deskripsi,
            'image_url'   => $this->image_url,
            'color'       => $this->warna ?: '#2563EB',
            'kind'        => 'custom',
        ];
    }

    public static function forTarget(string $targetType, int $targetId)
    {
        return static::where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->orderByDesc('id_badge_award')
            ->get();
    }
}
