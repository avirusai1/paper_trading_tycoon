<?php
declare(strict_types=1);

namespace App\GameEngine\Exceptions;

/**
 * Thrown when a career title update fails (e.g. no title defined for level).
 */
final class CareerException extends GameEngineException
{
    public static function noTitleForLevel(int $level): self
    {
        return new self(
            "No career title is defined for level {$level}. Seed career_titles table.",
            'career_no_title_for_level',
        );
    }
}
