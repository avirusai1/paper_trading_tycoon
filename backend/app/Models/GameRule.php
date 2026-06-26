<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Database-driven game balance configuration.
 * RulesEngine reads from this model with cache-aside pattern.
 */
final class GameRule extends Model
{
    use HasFactory;
    protected $fillable = ['key', 'group', 'value', 'value_type', 'description', 'is_overridable'];

    protected function casts(): array
    {
        return ['is_overridable' => 'boolean'];
    }

    /**
     * Return the value cast to its declared value_type.
     */
    public function typedValue(): mixed
    {
        return match ($this->value_type) {
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }
}
