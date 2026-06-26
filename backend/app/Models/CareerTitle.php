<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Career title definitions mapped to level ranges.
 */
final class CareerTitle extends Model
{
    use HasFactory;
    protected $fillable = [
        'title', 'min_level', 'max_level', 'description', 'icon_url', 'color_hex', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_level' => 'integer',
            'max_level' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public static function forLevel(int $level): ?self
    {
        return self::where('min_level', '<=', $level)
            ->where('max_level', '>=', $level)
            ->first();
    }
}
