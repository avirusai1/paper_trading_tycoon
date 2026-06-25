<?php

declare(strict_types=1);

/**
 * Paper Trading Tycoon — Gamification Configuration
 *
 * Default XP weights and game economy values used before the Rules Engine
 * database records are seeded. All runtime reads should go through
 * RulesEngine::get() which caches and overrides these values.
 */
return [
    /*
    |--------------------------------------------------------------------------
    | XP Sources (default weights — tuned via Rules Engine admin UI)
    |--------------------------------------------------------------------------
    */
    'xp' => [
        'trade_buy' => 10,
        'trade_sell' => 10,
        'daily_login' => 25,
        'challenge_completed_easy' => 50,
        'challenge_completed_medium' => 100,
        'challenge_completed_hard' => 200,
        'achievement_unlocked' => 75,
        'referral_joined' => 150,
        'first_trade' => 100, // One-time bonus
    ],

    /*
    |--------------------------------------------------------------------------
    | Daily XP Cap (per source type — Rules Engine overrides)
    |--------------------------------------------------------------------------
    */
    'daily_xp_cap' => [
        'trade_buy' => 100,
        'trade_sell' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Coin Rewards (default — Rules Engine overrides)
    |--------------------------------------------------------------------------
    */
    'coins' => [
        'challenge_easy' => 50,
        'challenge_medium' => 100,
        'challenge_hard' => 250,
        'achievement_bronze' => 100,
        'achievement_silver' => 250,
        'achievement_gold' => 500,
        'level_up' => 200,
        'daily_login' => 10,
        'referral' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | League Configuration
    |--------------------------------------------------------------------------
    */
    'leagues' => [
        'bronze' => ['rank' => 1, 'promote_top_percent' => 0.25, 'demote_bottom_percent' => 0.0],
        'silver' => ['rank' => 2, 'promote_top_percent' => 0.25, 'demote_bottom_percent' => 0.25],
        'gold' => ['rank' => 3, 'promote_top_percent' => 0.25, 'demote_bottom_percent' => 0.25],
        'platinum' => ['rank' => 4, 'promote_top_percent' => 0.25, 'demote_bottom_percent' => 0.25],
        'diamond' => ['rank' => 5, 'promote_top_percent' => 0.0, 'demote_bottom_percent' => 0.25],
    ],

    /*
    |--------------------------------------------------------------------------
    | Career Title Mapping (Level → Title)
    |--------------------------------------------------------------------------
    | This is the authoritative mapping used by the Level Engine.
    | Stored in Rules Engine DB; this array is the initial seed value.
    */
    'career_titles' => [
        ['min_level' => 1, 'max_level' => 5, 'title' => 'Student Trader'],
        ['min_level' => 6, 'max_level' => 10, 'title' => 'Intern Trader'],
        ['min_level' => 11, 'max_level' => 15, 'title' => 'Junior Trader'],
        ['min_level' => 16, 'max_level' => 20, 'title' => 'Retail Trader'],
        ['min_level' => 21, 'max_level' => 30, 'title' => 'Professional Trader'],
        ['min_level' => 31, 'max_level' => 40, 'title' => 'Senior Trader'],
        ['min_level' => 41, 'max_level' => 50, 'title' => 'Fund Manager'],
        ['min_level' => 51, 'max_level' => 60, 'title' => 'Portfolio Manager'],
        ['min_level' => 61, 'max_level' => 75, 'title' => 'Hedge Fund Manager'],
        ['min_level' => 76, 'max_level' => PHP_INT_MAX, 'title' => 'Market Legend'],
    ],
];
