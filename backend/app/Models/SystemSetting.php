<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Key-value runtime system configuration.
 */
final class SystemSetting extends Model
{
    protected $fillable = ['key', 'group', 'value', 'value_type', 'description', 'is_public'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    public function typedValue(): mixed
    {
        return match ($this->value_type) {
            'integer' => (int) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($this->value, true),
            default   => $this->value,
        };
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting?->typedValue() ?? $default;
    }
}
