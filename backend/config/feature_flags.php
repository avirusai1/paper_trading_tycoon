<?php

declare(strict_types=1);

/**
 * Paper Trading Tycoon — Feature Flags Configuration
 *
 * Compile-time fallback defaults for feature flags.
 * Runtime values are managed in the database via the Admin Panel
 * and served via GET /api/v1/feature-flags.
 *
 * V1 launch: all optional features are disabled by default.
 * The Flutter app checks flags before rendering feature entry points.
 * The API also enforces flags server-side as defense in depth.
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Flag Defaults (if DB record not found)
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'crypto_trading' => (bool) env('FLAG_CRYPTO_TRADING', false),
        'options_trading' => (bool) env('FLAG_OPTIONS_TRADING', false),
        'battle_pass' => (bool) env('FLAG_BATTLE_PASS', false),
        'ai_coach' => (bool) env('FLAG_AI_COACH', false),
        'copy_trading' => (bool) env('FLAG_COPY_TRADING', false),
        'tournaments' => (bool) env('FLAG_TOURNAMENTS', false),
        'advanced_analytics' => 'premium', // premium-gated, not boolean
    ],

    /*
    |--------------------------------------------------------------------------
    | Flag Cache TTL
    |--------------------------------------------------------------------------
    | How long to cache the flags payload before re-reading from DB.
    | Admin flag changes take effect within this window.
    */
    'cache_ttl_seconds' => 300, // 5 minutes
    'cache_key' => 'feature_flags_payload',
];
