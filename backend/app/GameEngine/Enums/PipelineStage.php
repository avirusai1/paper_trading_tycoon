<?php
declare(strict_types=1);

namespace App\GameEngine\Enums;

/**
 * Ordered stages of the Game Engine processing pipeline.
 *
 * Each stage corresponds to a Processor. Stages are executed in the order
 * defined here. The pipeline short-circuits if the player cannot participate.
 */
enum PipelineStage: string
{
    case XP          = 'xp';
    case Level       = 'level';
    case Career      = 'career';
    case Missions    = 'missions';
    case Achievements = 'achievements';
    case League      = 'league';
    case Season      = 'season';
    case Rewards     = 'rewards';
}
