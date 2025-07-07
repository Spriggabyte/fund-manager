<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundRevision extends Model
{
    protected $fillable = [
        'fund_id',
        'user_id',
        'name',
        'class',
        'data',
        'change_summary',
        'changed_field',
        'old_value',
        'new_value',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function fund(): BelongsTo
    {
        return $this->belongsTo(Fund::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDiffAttribute(): array
    {
        if (!$this->changed_field) {
            return [];
        }

        return [
            'field' => $this->changed_field,
            'old' => $this->old_value,
            'new' => $this->new_value,
        ];
    }
}
