<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GameRule;
use Illuminate\Database\Seeder;

/**
 * Seeds all game balance configuration into the game_rules table.
 * Every value here was previously hardcoded in config/gamification.php —
 * moving them to DB enables runtime rebalancing without code deploys.
 */
final class GameRulesSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            // ── XP per action ─────────────────────────────────────────────
            ['key' => 'xp.trade_buy',                      'group' => 'xp',      'value' => '10',   'value_type' => 'integer', 'description' => 'XP awarded per buy trade'],
            ['key' => 'xp.trade_sell',                     'group' => 'xp',      'value' => '10',   'value_type' => 'integer', 'description' => 'XP awarded per sell trade'],
            ['key' => 'xp.daily_login',                    'group' => 'xp',      'value' => '25',   'value_type' => 'integer', 'description' => 'XP awarded for daily login'],
            ['key' => 'xp.challenge_easy',                 'group' => 'xp',      'value' => '50',   'value_type' => 'integer', 'description' => 'XP for completing easy challenge'],
            ['key' => 'xp.challenge_medium',               'group' => 'xp',      'value' => '100',  'value_type' => 'integer', 'description' => 'XP for completing medium challenge'],
            ['key' => 'xp.challenge_hard',                 'group' => 'xp',      'value' => '200',  'value_type' => 'integer', 'description' => 'XP for completing hard challenge'],
            ['key' => 'xp.achievement_unlocked',           'group' => 'xp',      'value' => '75',   'value_type' => 'integer', 'description' => 'XP for any achievement unlock'],
            ['key' => 'xp.referral_joined',                'group' => 'xp',      'value' => '150',  'value_type' => 'integer', 'description' => 'XP for successful referral'],
            ['key' => 'xp.first_trade',                    'group' => 'xp',      'value' => '100',  'value_type' => 'integer', 'description' => 'Bonus XP for first-ever trade'],
            // ── Daily XP caps ──────────────────────────────────────────────
            ['key' => 'xp.daily_cap.trade_buy',            'group' => 'xp',      'value' => '100',  'value_type' => 'integer', 'description' => 'Max XP from buy trades per day'],
            ['key' => 'xp.daily_cap.trade_sell',           'group' => 'xp',      'value' => '100',  'value_type' => 'integer', 'description' => 'Max XP from sell trades per day'],
            // ── Coin rewards ───────────────────────────────────────────────
            ['key' => 'coins.challenge_easy',              'group' => 'coins',   'value' => '50',   'value_type' => 'integer', 'description' => 'Coins for easy challenge'],
            ['key' => 'coins.challenge_medium',            'group' => 'coins',   'value' => '100',  'value_type' => 'integer', 'description' => 'Coins for medium challenge'],
            ['key' => 'coins.challenge_hard',              'group' => 'coins',   'value' => '250',  'value_type' => 'integer', 'description' => 'Coins for hard challenge'],
            ['key' => 'coins.achievement_bronze',          'group' => 'coins',   'value' => '100',  'value_type' => 'integer', 'description' => 'Coins for bronze achievement'],
            ['key' => 'coins.achievement_silver',          'group' => 'coins',   'value' => '250',  'value_type' => 'integer', 'description' => 'Coins for silver achievement'],
            ['key' => 'coins.achievement_gold',            'group' => 'coins',   'value' => '500',  'value_type' => 'integer', 'description' => 'Coins for gold achievement'],
            ['key' => 'coins.achievement_platinum',        'group' => 'coins',   'value' => '1000', 'value_type' => 'integer', 'description' => 'Coins for platinum achievement'],
            ['key' => 'coins.level_up',                    'group' => 'coins',   'value' => '200',  'value_type' => 'integer', 'description' => 'Coins awarded on level-up'],
            ['key' => 'coins.daily_login',                 'group' => 'coins',   'value' => '10',   'value_type' => 'integer', 'description' => 'Coins for daily login'],
            ['key' => 'coins.referral',                    'group' => 'coins',   'value' => '500',  'value_type' => 'integer', 'description' => 'Coins for successful referral (both parties)'],
            // ── League promotion/demotion thresholds ───────────────────────
            ['key' => 'leagues.bronze.promote_top_percent',   'group' => 'leagues', 'value' => '25',   'value_type' => 'float',   'description' => 'Top % promoted from Bronze'],
            ['key' => 'leagues.bronze.demote_bottom_percent', 'group' => 'leagues', 'value' => '0',    'value_type' => 'float',   'description' => 'Bottom % demoted from Bronze (none — lowest tier)'],
            ['key' => 'leagues.silver.promote_top_percent',   'group' => 'leagues', 'value' => '25',   'value_type' => 'float',   'description' => 'Top % promoted from Silver'],
            ['key' => 'leagues.silver.demote_bottom_percent', 'group' => 'leagues', 'value' => '25',   'value_type' => 'float',   'description' => 'Bottom % demoted from Silver'],
            ['key' => 'leagues.gold.promote_top_percent',     'group' => 'leagues', 'value' => '25',   'value_type' => 'float',   'description' => 'Top % promoted from Gold'],
            ['key' => 'leagues.gold.demote_bottom_percent',   'group' => 'leagues', 'value' => '25',   'value_type' => 'float',   'description' => 'Bottom % demoted from Gold'],
            ['key' => 'leagues.platinum.promote_top_percent', 'group' => 'leagues', 'value' => '25',   'value_type' => 'float',   'description' => 'Top % promoted from Platinum'],
            ['key' => 'leagues.platinum.demote_bottom_percent', 'group' => 'leagues', 'value' => '25',  'value_type' => 'float',   'description' => 'Bottom % demoted from Platinum'],
            ['key' => 'leagues.diamond.promote_top_percent',  'group' => 'leagues', 'value' => '0',    'value_type' => 'float',   'description' => 'Top % promoted from Diamond (none — highest tier)'],
            ['key' => 'leagues.diamond.demote_bottom_percent', 'group' => 'leagues', 'value' => '25',   'value_type' => 'float',   'description' => 'Bottom % demoted from Diamond'],
            // ── Season settings ────────────────────────────────────────────
            ['key' => 'seasons.duration_days',             'group' => 'seasons', 'value' => '28',   'value_type' => 'integer', 'description' => 'Default season duration in days'],
            ['key' => 'seasons.grace_period_hours',        'group' => 'seasons', 'value' => '24',   'value_type' => 'integer', 'description' => 'Hours after season end before rewards are distributed'],
            // ── Mission refresh schedule ───────────────────────────────────
            ['key' => 'missions.daily_reset_hour_ist',     'group' => 'missions', 'value' => '0',    'value_type' => 'integer', 'description' => 'Hour (IST) at which daily missions reset (0 = midnight)'],
            ['key' => 'missions.weekly_reset_day',         'group' => 'missions', 'value' => '1',    'value_type' => 'integer', 'description' => 'Day of week for weekly reset (1=Monday)'],
            ['key' => 'missions.daily_count',              'group' => 'missions', 'value' => '3',    'value_type' => 'integer', 'description' => 'Number of daily missions shown to each user'],
            ['key' => 'missions.weekly_count',             'group' => 'missions', 'value' => '2',    'value_type' => 'integer', 'description' => 'Number of weekly missions shown to each user'],
            // ── Market data settings ───────────────────────────────────────
            ['key' => 'market.quote_cache_ttl_seconds',    'group' => 'market',  'value' => '60',   'value_type' => 'integer', 'description' => 'How long to cache stock quotes'],
            ['key' => 'market.open_time_ist',              'group' => 'market',  'value' => '09:15', 'value_type' => 'string',  'description' => 'NSE market open time (IST)'],
            ['key' => 'market.close_time_ist',             'group' => 'market',  'value' => '15:30', 'value_type' => 'string',  'description' => 'NSE market close time (IST)'],
            // ── Starting conditions ────────────────────────────────────────
            ['key' => 'game.starting_cash_paise',          'group' => 'game',    'value' => '100000000', 'value_type' => 'integer', 'description' => 'Virtual cash granted on registration (₹10,00,000)'],
            ['key' => 'game.max_holdings_per_user',        'group' => 'game',    'value' => '20',   'value_type' => 'integer', 'description' => 'Maximum distinct stocks a user can hold simultaneously'],
            // ── Anti-cheat velocity limits ─────────────────────────────────
            ['key' => 'anticheat.max_trades_per_minute',   'group' => 'anticheat', 'value' => '10',  'value_type' => 'integer', 'description' => 'Max trades a user can place per minute'],
            ['key' => 'anticheat.max_referrals_per_device', 'group' => 'anticheat', 'value' => '3',   'value_type' => 'integer', 'description' => 'Max referrals originating from single device'],
        ];

        foreach ($rules as $rule) {
            GameRule::updateOrCreate(['key' => $rule['key']], $rule);
        }
    }
}
