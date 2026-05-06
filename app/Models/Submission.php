<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    protected $fillable = [
        'assignment_id',
        'mahasiswa_profile_id',
        'file_path',
        'notes',
        'submitted_at',
        'grade',
        'comment',
        'graded_by',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'graded_at'    => 'datetime',
            'grade'        => 'integer',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ClassAssignment::class, 'assignment_id');
    }

    public function mahasiswaProfile(): BelongsTo
    {
        return $this->belongsTo(MahasiswaProfile::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function isGraded(): bool
    {
        return $this->grade !== null;
    }

    public function gradeBadge(): string
    {
        if ($this->grade === null) return 'Belum dinilai';
        return (string) $this->grade;
    }
}
