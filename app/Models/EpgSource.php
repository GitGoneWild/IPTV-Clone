<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EpgSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'file_path',
        'is_active',
        'last_import_at',
        'last_import_status',
        'programs_count',
        'channels_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_import_at' => 'datetime',
            'programs_count' => 'integer',
            'channels_count' => 'integer',
        ];
    }

    /**
     * Scope for active EPG sources.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
