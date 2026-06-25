# Paper Trading Tycoon — Database Implementation Report

**Generated:** June 2025  
**Scope:** Complete V1 persistence layer — migrations, models, factories, seeders  
**Commands to run:** `php artisan migrate && php artisan db:seed`

---

## Final Verification Checklist

| Check | Result |
|-------|--------|
| `declare(strict_types=1)` in all files | ✅ 114/114 files |
| `BIGINT` for all monetary (paise) columns | ✅ 100% — zero DECIMAL/FLOAT on paise columns |
| `DECIMAL` only in % display columns (change_percent, return_percent etc.) | ✅ 6 display-only decimal columns |
| Soft deletes only on `users` | ✅ Correct |
| Coin transaction idempotency index | ✅ `UNIQUE(user_id, source_type, source_id)` |
| XP log idempotency index | ✅ `UNIQUE(user_id, source, source_id)` |
| Append-only models (`UPDATED_AT = null`) | ✅ 11 models: Trade, OrderEvent, XpLog, CoinTransaction, UserAchievement, PortfolioSnapshot, LeaderboardEntry, UserNotification, RewardHistory, AdminLog, AuditLog |
| Migration dependency order | ✅ All FK targets migrate before referencing tables |
| PHPStan strict_types | ✅ All files |

---

## Tables Created (46 tables)

### Authentication & Users
| Table | Purpose |
|-------|---------|
| `users` | Core auth credentials, status, referral code, soft-delete |
| `user_profiles` | Extended profile — display name, avatar, city, login streaks |
| `user_devices` | Registered devices for FCM push and anti-fraud |
| `personal_access_tokens` | Sanctum API tokens |

### Market Data
| Table | Purpose |
|-------|---------|
| `stocks` | Stock master catalogue — metadata, sector, exchange |
| `stock_prices` | Current (latest) quote per stock — 1:1 with stocks |
| `stock_daily_history` | EOD OHLCV per stock — charts and historical P&L |
| `watchlists` | User-defined watchlist groups |
| `watchlist_items` | Individual stocks within a watchlist |

### Trading
| Table | Purpose |
|-------|---------|
| `orders` | All trade orders — market/limit/stop/bracket, partial fills |
| `order_events` | Append-only order lifecycle audit trail |
| `trades` | Immutable executed fill records — source of truth for P&L |
| `holdings` | Current open positions per user |
| `portfolio_snapshots` | Point-in-time portfolio value snapshots |
| `wallets` | Virtual cash balance + materialized coin balance |

### Game Engine
| Table | Purpose |
|-------|---------|
| `game_rules` | DB-driven game balance config (XP, coins, leagues, market timings) |
| `levels` | Level 1–100 thresholds with XP requirements and rewards |
| `career_titles` | Title definitions mapped to level ranges |
| `user_levels` | Current XP and level state per user |
| `xp_logs` | Append-only XP transaction audit log |
| `achievements` | Achievement definition catalogue |
| `user_achievements` | Per-user achievement unlock records |
| `missions` | Mission/challenge template catalogue |
| `user_missions` | Per-user mission progress within a cycle |
| `leagues` | League tier definitions (Bronze → Diamond) |
| `seasons` | Season competition period definitions |
| `user_leagues` | Per-user league membership per season |
| `season_rewards` | End-of-season reward tiers per league |
| `leaderboards` | Leaderboard definitions |
| `leaderboard_entries` | Computed rankings — refreshed by scheduled job |

### Economy
| Table | Purpose |
|-------|---------|
| `coin_transactions` | Append-only coin ledger (ADR-004) |
| `reward_history` | Unified XP + coin reward audit log |

### Store
| Table | Purpose |
|-------|---------|
| `store_categories` | Coin store category groups |
| `store_items` | Item catalogue with effects, level requirements |
| `user_inventory` | Items owned by users |

### Premium
| Table | Purpose |
|-------|---------|
| `subscription_plans` | Monthly and annual plan definitions |
| `subscriptions` | User premium subscription records |

### Referrals
| Table | Purpose |
|-------|---------|
| `referrals` | Referral relationships with anti-fraud status |
| `referral_rewards` | Rewards granted to referrer and referee |

### Notifications
| Table | Purpose |
|-------|---------|
| `notifications` | Notification templates and broadcast messages |
| `user_notifications` | Per-user delivery and read state |

### Admin & Config
| Table | Purpose |
|-------|---------|
| `feature_flags` | Runtime feature toggles with % rollout |
| `system_settings` | Key-value runtime configuration |
| `admin_logs` | Admin action audit trail |
| `audit_logs` | General model change audit trail |

### Queue Infrastructure
| Table | Purpose |
|-------|---------|
| `jobs` | Laravel database queue jobs (V1 queue driver) |
| `job_batches` | Batched job tracking |
| `failed_jobs` | Dead letter queue (ADR-003) |

---

## Models Created (44 models)

`User`, `UserProfile`, `UserDevice`, `Stock`, `StockPrice`, `StockDailyHistory`, `Watchlist`, `WatchlistItem`, `Order`, `OrderEvent`, `Trade`, `Holding`, `PortfolioSnapshot`, `Wallet`, `GameRule`, `Level`, `CareerTitle`, `UserLevel`, `XpLog`, `Achievement`, `UserAchievement`, `Mission`, `UserMission`, `League`, `Season`, `UserLeague`, `SeasonReward`, `Leaderboard`, `LeaderboardEntry`, `CoinTransaction`, `RewardHistory`, `StoreCategory`, `StoreItem`, `UserInventory`, `SubscriptionPlan`, `Subscription`, `Referral`, `ReferralReward`, `Notification`, `UserNotification`, `FeatureFlag`, `SystemSetting`, `AdminLog`, `AuditLog`

---

## Factories Created (11 factories)

`UserFactory` (Indian names, realistic referral codes), `UserProfileFactory` (Indian cities/states), `StockFactory` (NSE symbols), `OrderFactory`, `TradeFactory`, `HoldingFactory`, `WalletFactory`, `UserLevelFactory`, `CoinTransactionFactory`, `AchievementFactory`, `MissionFactory`

---

## Seeders Created (13 seeders)

| Seeder | Records |
|--------|---------|
| `GameRulesSeeder` | 45 game balance rules |
| `LevelsSeeder` | 100 levels (progressive XP curve: 100 × level^1.5) |
| `CareerTitlesSeeder` | 10 career titles |
| `LeaguesSeeder` | 5 league tiers |
| `FeatureFlagsSeeder` | 12 feature flags (all disabled by default) |
| `SystemSettingsSeeder` | 11 system settings |
| `StoreCategoriesSeeder` | 5 store categories |
| `StoreItemsSeeder` | 10 store items |
| `SubscriptionPlansSeeder` | 2 plans (monthly ₹99, annual ₹799) |
| `AchievementsSeeder` | 16 achievement definitions |
| `MissionsSeeder` | 13 mission templates (daily, weekly, tutorial) |
| `StocksSeeder` | 20 Nifty 50 stocks with reference prices |
| `DatabaseSeeder` | Orchestrator — runs all in dependency order |

---

## Indexes Created

### Performance-Critical Composite Indexes

| Table | Index | Purpose |
|-------|-------|---------|
| `users` | `(status, is_premium)` | Filter active premium users |
| `orders` | `(user_id, status)` | Open order queries per user |
| `orders` | `(user_id, symbol, status)` | Portfolio holdings check before order |
| `orders` | `(stock_id, status, side)` | Market depth queries |
| `trades` | `(user_id, symbol)` | P&L calculation per holding |
| `trades` | `(user_id, executed_at)` | Activity timeline |
| `holdings` | `(user_id, quantity)` | Active holdings filter |
| `xp_logs` | `UNIQUE(user_id, source, source_id)` | XP grant idempotency |
| `coin_transactions` | `UNIQUE(user_id, source_type, source_id)` | Coin award idempotency (ADR-004) |
| `leaderboard_entries` | `(leaderboard_id, rank_position)` | O(1) paginated leaderboard reads |
| `leaderboard_entries` | `(leaderboard_id, score_value)` | Score-based sort queries |
| `user_leagues` | `(season_id, tier, season_portfolio_value_paise)` | Season ranking queries |
| `portfolio_snapshots` | `UNIQUE(user_id, snapshot_date, snapshot_type)` | Prevent duplicate snapshots |
| `stock_daily_history` | `UNIQUE(stock_id, trading_date)` | EOD data deduplication |
| `user_missions` | `(user_id, status, expires_at)` | Active mission queries |
| `user_notifications` | `(user_id, is_read, created_at)` | Unread notification count |

---

## Key Architecture Decisions

**Monetary storage:** All paise values use `BIGINT UNSIGNED`. The only `DECIMAL` columns are percentage/display values (`change_percent`, `season_return_percent` etc.) which never participate in financial arithmetic.

**Append-only ledgers:** `coin_transactions` and `xp_logs` have `UPDATED_AT = null` at the model level and no update paths. Balances are materialized in `wallets.coin_balance` and `user_levels.current_xp`.

**Idempotency at DB level:** Both ledger tables have `UNIQUE(user_id, source_type, source_id)` — duplicate inserts from listener retries are rejected at the database layer, not just the application layer.

**Order schema future-proofing:** `orders.order_type` includes `market`, `limit`, `stop`, `stop_limit`, `bracket`. `orders.status` includes `partially_filled`. `order_events` is a separate append-only table for lifecycle audit. No schema changes needed to implement any order type.

**Soft deletes:** Only `users` has `SoftDeletes`. All child records use `cascadeOnDelete` — a deleted user's data is removed cleanly. Trades and orders use `restrictOnDelete` on the stock FK to prevent orphaned financial records.

**Leaderboard pattern:** `leaderboard_entries` is a pre-computed cache table, not a view. This is intentional — leaderboard computation is expensive and done in a scheduled job, not on every request.

---

## Scalability Notes

**Phase 1 (< 50K users — shared hosting):**
- Single `default` queue (ADR-003)
- File cache for FeatureFlagService and RulesEngine
- Leaderboard job runs every 30 minutes via cron

**Phase 2 (100K users — VPS/Redis):**
- Partition queues: `trades`, `game`, `notifications`, `analytics`
- Redis cache for leaderboards, feature flags, rules engine
- `portfolio_snapshots` sharded by user_id range
- Consider read replica for leaderboard queries

**Phase 3 (1M+ users):**
- `coin_transactions` and `xp_logs` candidate for partitioning by `user_id % N`
- `stock_daily_history` archive old years to cold storage
- `leaderboard_entries` → Redis sorted sets for real-time rankings
- `trades` table: archive records > 2 years to `trades_archive`

---

## Potential Risks

| Risk | Mitigation |
|------|-----------|
| `wallets.coin_balance` drift from `coin_transactions` SUM | Reconciliation job every 24h; alert if delta > 0 |
| `holdings` value staleness | Snapshot job every 30min during market hours; UI shows "as of" timestamp |
| Leaderboard contention on `leaderboard_entries` bulk upsert | Use `INSERT ... ON DUPLICATE KEY UPDATE`; run during off-peak |
| `user_missions` expired rows accumulating | Archived by a nightly cleanup job; indexed on `expires_at` |
| Large `xp_logs` / `coin_transactions` tables at scale | Add `created_at` range partitioning at 10M rows per user cohort |
| `orders.idempotency_key` UNIQUE index on high-write table | Covered by B-tree index; length capped at 64 chars |

---

## Future Improvements

1. **Database migrations for market hours exceptions** — add a `market_holidays` table keyed by date so the application can determine market status without hardcoded IST time logic.
2. **`user_xp_daily_caps` table** — track per-user, per-source, per-day XP totals for the daily XP cap enforcement in `XPEngine`. Currently requires an aggregation query.
3. **`user_trade_stats` denormalized table** — pre-aggregate total_trades, win_rate, best_return for profile display. Avoids full `trades` scans on profile loads.
4. **`stock_sectors` lookup table** — currently sector is a free-text string on `stocks`. Normalizing it enables sector-filtered leaderboards without LIKE queries.
5. **`user_notifications` bulk-read endpoint** — add a `read_all_before` timestamp column to support "mark all as read" without N individual UPDATE statements.
