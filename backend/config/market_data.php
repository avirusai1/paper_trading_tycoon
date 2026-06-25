<?php

declare(strict_types=1);

/**
 * Paper Trading Tycoon — Market Data Configuration
 *
 * Configuration for the Market Data Layer (provider adapter, cache TTLs).
 * The Flutter app never calls the stock data provider directly — all
 * price data flows through MarketDataService.
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Active Provider
    |--------------------------------------------------------------------------
    | Determines which ProviderAdapter implementation is resolved from
    | the container. Switch providers here without touching service classes.
    | Valid values: 'alpha_vantage', 'yahoo_finance', 'nse_vendor'
    */
    'provider' => env('MARKET_DATA_PROVIDER', 'alpha_vantage'),

    /*
    |--------------------------------------------------------------------------
    | Provider Credentials
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'alpha_vantage' => [
            'base_url' => env('STOCK_DATA_BASE_URL', 'https://www.alphavantage.co/query'),
            'api_key' => env('STOCK_DATA_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTLs (seconds)
    |--------------------------------------------------------------------------
    | Quote TTL is deliberately short during market hours.
    | Symbol master changes infrequently — 24h cache is safe.
    */
    'cache_ttl' => [
        'quote_market_open' => 30,    // 30s during trading hours
        'quote_market_closed' => 300, // 5min when market is closed
        'symbol_master' => 86400,     // 24 hours
        'historical' => 3600,         // 1 hour
        'market_status' => 60,        // 1 minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Single-Flight Cache Key Prefix
    |--------------------------------------------------------------------------
    | Used to deduplicate concurrent quote requests for the same symbol.
    | Prevents cache stampede on market open.
    */
    'single_flight_prefix' => 'mds_inflight_',

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    */
    'timeout_seconds' => 10,
];
