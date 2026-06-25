# ADR-002: Market Data Provider Strategy

**Status:** Pending Decision  
**Date:** June 2025  
**Required before:** Milestone 3 (Market Data Layer)

---

## Context

The application requires real-time (or near-real-time) Indian stock market data. Prices drive the simulation — accuracy and latency directly affect game credibility. A provider must be chosen before the Market Data Layer (`MarketDataService`, `MarketDataAdapter`) can be implemented.

## Constraints

- Provider API keys must exist **only in Laravel backend config** — Flutter never calls a provider directly (security rule).
- All prices transmitted over the API must be in **paise integers** (ADR-004).
- V1 infrastructure is shared PHP hosting — HTTP polling only (no WebSocket in shared hosting process model).
- Budget: cost must be justifiable at < 1,000 daily active users.

## Candidate Providers

| Provider | Coverage | Delay | Free Tier | Notes |
|----------|----------|-------|-----------|-------|
| **NSE India (unofficial)** | NSE only | 15 min | Yes | No official API; scraped; fragile |
| **Alpha Vantage** | Global + BSE | 15–60 min | 25 req/day | Insufficient for active polling |
| **Twelve Data** | NSE + BSE | Real-time | 800 req/day | Realistic for V1 volume |
| **Yahoo Finance (unofficial)** | NSE + BSE | 15 min | Yes | Unofficial; ToS risk |
| **Marketstack** | Partial India | EOD | 100 req/mo | EOD only; not suitable |
| **Financial Modeling Prep** | Limited India | 15 min | 250 req/day | Limited NSE coverage |

## Recommended Path

**V1:** Twelve Data free tier (800 API calls/day) with aggressive caching.

- Cache quotes in Redis/file cache for 60 seconds (polling frequency cap).
- Batch quote endpoint to reduce API calls — fetch top 50 watched stocks in one call.
- Background Laravel job refreshes the cache on a schedule.

**V2 (> 10K DAU):** Evaluate paid Twelve Data plan or NSE Data Feed official licensing.

## Architecture Contract

Regardless of provider chosen, the adapter pattern **must** be preserved:

```
Laravel MarketDataService
  └── MarketDataAdapter (interface)
        ├── TwelveDataAdapter (implements)
        ├── AlphaVantageAdapter (implements)    ← swap without touching service
        └── MockMarketDataAdapter (test usage)
```

The `StockQuoteDTO` is the single normalized output — all paise, all Carbon timestamps. Swapping providers means replacing one adapter file.

## Decision Pending

**Action required:** Evaluate Twelve Data free tier against expected polling volume before Milestone 3 begins. If insufficient, budget must be allocated for a paid tier or an alternative provider selected.

**Decision owner:** Technical Lead  
**Decision deadline:** Before Milestone 3 kickoff

## Consequences

- Provider keys in `.env` as `MARKET_DATA_API_KEY` and `MARKET_DATA_BASE_URL`.
- Mock adapter ships with the skeleton so Milestone 1–2 development proceeds without a real provider.
- Cache TTL configurable in `config/gamification.php` under `market_data.quote_cache_ttl_seconds`.
