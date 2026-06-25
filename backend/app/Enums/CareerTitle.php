<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Paper Trading Tycoon — Career Title Enum
 *
 * Narrative progression layer on top of numeric levels.
 * Level-to-title mapping is managed by the Rules Engine (configurable).
 * The enum provides type safety for title references throughout the codebase.
 */
enum CareerTitle: string
{
    case StudentTrader      = 'Student Trader';      // Levels 1–5
    case InternTrader       = 'Intern Trader';       // Levels 6–10
    case JuniorTrader       = 'Junior Trader';       // Levels 11–15
    case RetailTrader       = 'Retail Trader';       // Levels 16–20
    case ProfessionalTrader = 'Professional Trader'; // Levels 21–30
    case SeniorTrader       = 'Senior Trader';       // Levels 31–40
    case FundManager        = 'Fund Manager';        // Levels 41–50
    case PortfolioManager   = 'Portfolio Manager';   // Levels 51–60
    case HedgeFundManager   = 'Hedge Fund Manager';  // Levels 61–75
    case MarketLegend       = 'Market Legend';       // Levels 76+
}
