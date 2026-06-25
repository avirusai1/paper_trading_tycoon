<?php

declare(strict_types=1);

/**
 * Paper Trading Tycoon — Trading Configuration
 *
 * Static defaults for the Trading Engine and Portfolio module.
 * All values here are the hardcoded fallbacks; the Rules Engine
 * overrides these at runtime with database-sourced values.
 * Never read these directly in service classes — always use RulesEngine.
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Starting Virtual Cash
    |--------------------------------------------------------------------------
    | The amount credited to a new user's virtual wallet on registration.
    | Stored in paise (integer) to avoid floating-point precision issues.
    | See ADR-004 for the monetary representation decision.
    |
    | ₹10,00,000 = 100,000,00 paise = 100000000
    */
    'starting_cash_paise' => (int) env('STARTING_VIRTUAL_CASH_PAISE', 100000000),

    /*
    |--------------------------------------------------------------------------
    | Market Hours (IST)
    |--------------------------------------------------------------------------
    | NSE/BSE regular trading hours in Indian Standard Time.
    | Format: 'HH:MM'
    | The authoritative market status check uses the Rules Engine;
    | these values are the compile-time defaults before DB is seeded.
    */
    'market_open_time' => '09:15',
    'market_close_time' => '15:30',

    /*
    |--------------------------------------------------------------------------
    | Market Days
    |--------------------------------------------------------------------------
    | 1 = Monday, 7 = Sunday (PHP date 'N' format).
    */
    'market_days' => [1, 2, 3, 4, 5], // Monday–Friday

    /*
    |--------------------------------------------------------------------------
    | Idempotency Key TTL
    |--------------------------------------------------------------------------
    | How long (in seconds) to retain idempotency keys for duplicate
    | trade request detection. 24 hours covers all realistic retry windows.
    */
    'idempotency_ttl_seconds' => 86400,

    /*
    |--------------------------------------------------------------------------
    | Order Limits (default — overridden by Rules Engine)
    |--------------------------------------------------------------------------
    */
    'max_order_quantity' => 10000,
    'min_order_quantity' => 1,
];
