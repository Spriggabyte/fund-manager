<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fund extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'class',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function getDataAttribute(mixed $value): array
    {
        if (is_null($value)) {
            return [];
        }
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        return is_array($value) ? $value : [];
    }

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

    /**
     * Get a nested value from the fund's data array using dot notation.
     */
    public function getDataValue(string $path): mixed
    {
        $keys = explode('.', $path);
        $current = $this->data ?? [];

        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return null;
            }
            $current = $current[$key];
        }

        return $current;
    }

    /**
     * Set a nested value in the fund's data array using dot notation.
     * Automatically casts numeric strings to int/float except for designated string fields.
     */
    public function setDataValue(array &$data, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $current = &$data;

        $stringFields = ['phone', 'email', 'website', 'date', 'name', 'description'];

        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                if ($value === '' || $value === null) {
                    unset($current[$key]);
                } elseif (is_numeric($value) && !in_array($key, $stringFields)) {
                    $current[$key] = is_float($value + 0) ? (float) $value : (int) $value;
                } else {
                    $current[$key] = $value;
                }
            } else {
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
        }
    }
}
