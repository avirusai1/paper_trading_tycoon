<?php
declare(strict_types=1);

namespace App\GameEngine\Enums;

/**
 * The trigger type used to advance mission progress.
 *
 * Mission templates in the `missions` table have a `category` column.
 * This enum maps those categories to the progression triggers that the
 * MissionProcessor understands, decoupling the storage format from the
 * engine's vocabulary.
 */
enum MissionProgressType: string
{
    case Trade      = 'trade';
    case BuyTrade   = 'trade_buy';
    case SellTrade  = 'trade_sell';
    case Login      = 'login';
    case Referral   = 'referral';
    case Portfolio  = 'portfolio';
    case Generic    = 'generic';
}
