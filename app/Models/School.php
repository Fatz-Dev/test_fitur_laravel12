<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = [
        'name',
        'jenjang',
        'address',
        'latitude',
        'longitude',
        'program',
        'kuota_kpm',
        'kuota_ppl',
        'contact_person',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function availableSlots(string $program): int
    {
        $kuota = $program === 'KPM' ? $this->kuota_kpm : $this->kuota_ppl;
        $taken = $this->registrations()
            ->where('program', $program)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        return max(0, $kuota - $taken);
    }

    public function acceptsProgram(string $program): bool
    {
        return $this->program === $program;
    }

    /**
     * Label tipe lokasi berdasarkan program.
     * KPM → Desa, PPL → Sekolah
     */
    public function locationType(): string
    {
        return $this->program === 'KPM' ? 'Desa' : 'Sekolah';
    }

    /**
     * Sebutan lokasi berdasarkan konteks program tertentu.
     */
    public static function labelFor(string $program): string
    {
        return $program === 'KPM' ? 'Desa' : 'Sekolah';
    }
}
