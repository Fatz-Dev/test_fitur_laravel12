<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassAssignment extends Model
{
    protected $table = 'class_assignments';

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'deadline',
        'attachment_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'assignment_id');
    }

    public function isPastDeadline(): bool
    {
        return $this->deadline->isPast();
    }
}
