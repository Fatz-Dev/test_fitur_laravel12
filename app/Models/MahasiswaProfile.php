<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MahasiswaProfile extends Model
{
    protected $fillable = [
        'user_id',
        'nim',
        'phone',
        'address',
        'latitude',
        'longitude',
        'microteaching_grade',
        'transkrip_path',
        'ktm_path',
        'surat_pengantar_path',
        'pas_foto_path',
        'status',
        'admin_note',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
