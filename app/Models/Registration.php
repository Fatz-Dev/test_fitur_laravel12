<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $fillable = [
        'mahasiswa_profile_id',
        'school_id',
        'gelombang_id',
        'program',
        'distance_km',
        'status',
        'note',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'distance_km'  => 'decimal:3',
            'confirmed_at' => 'datetime',
        ];
    }

    public function mahasiswaProfile(): BelongsTo
    {
        return $this->belongsTo(MahasiswaProfile::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(Gelombang::class);
    }
}
