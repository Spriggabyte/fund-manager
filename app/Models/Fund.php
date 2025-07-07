<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fund extends Model
{
    protected $fillable = [
        'name',
        'class',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(FundRevision::class)->orderBy('created_at', 'desc');
    }

    public function createRevision(string $changedField = null, $oldValue = null, $newValue = null, string $changeSummary = null): FundRevision
    {
        return $this->revisions()->create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'class' => $this->class,
            'data' => $this->data,
            'change_summary' => $changeSummary,
            'changed_field' => $changedField,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }
}
