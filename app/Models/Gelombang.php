<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gelombang extends Model
{
    protected $table = 'gelombang';

    protected $fillable = [
        'program',
        'nomor',
        'tahun_akademik',
        'tanggal_buka',
        'tanggal_tutup',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_buka'  => 'date',
            'tanggal_tutup' => 'date',
            'is_active'     => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function label(): string
    {
        return "Gelombang {$this->nomor} {$this->tahun_akademik}";
    }

    public function isOpen(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        $today = now()->toDateString();
        if ($this->tanggal_buka && $today < $this->tanggal_buka->toDateString()) {
            return false;
        }
        if ($this->tanggal_tutup && $today > $this->tanggal_tutup->toDateString()) {
            return false;
        }

        return true;
    }

    public static function activeFor(string $program): ?self
    {
        return static::where('program', $program)
            ->where('is_active', true)
            ->first();
    }
}
