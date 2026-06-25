# Paper Trading Tycoon — Master Architecture Document

**Version:** 1.1  
**Status:** Approved Blueprint (Architecture Review)  
**Last Updated:** June 2025  
**Primary Source of Truth:** `cursor_rules/project_rules.md`

---

## Document Purpose

This document is the permanent architectural reference for Paper Trading Tycoon. Every feature, module, API contract, folder placement, and development milestone must align with this blueprint. No implementation work begins without consulting this document.

**Scope:** Architecture and design only. This document does not contain application code, database schemas, migrations, or UI implementations.

---

## 1. Product Vision

### What is Paper Trading Tycoon?

Paper Trading Tycoon is a **game-first stock market simulator** built for the Indian market. Users receive virtual capital (₹10,00,000 on registration) and trade real-market stocks using live or near-live price data — without financial risk. The product is a game first and a paper trading simulator second: progression systems (career titles, XP, levels, leagues, achievements, challenges, leaderboards, seasons, and a ledger-based coin economy) transform learning and practice into an engaging, competitive experience.

The product sits at the intersection of **financial education**, **mobile gaming**, and **social competition**. It is not a brokerage. It does not execute real trades or hold real money.

### Target Audience

| Segment | Description | Primary Motivation |
|---------|-------------|-------------------|
| **Beginner Investors (18–30)** | College students and early-career professionals curious about markets | Learn without fear of loss |
| **Aspiring Traders (25–40)** | Individuals practicing strategies before real capital | Test strategies in realistic conditions |
| **Casual Gamers (16–35)** | Users drawn to progression, rankings, and daily challenges | Compete and climb leaderboards |
| **Finance Enthusiasts** | Users who follow markets recreationally | Track portfolio performance over time |

**Geographic Focus:** India (INR currency, NSE/BSE-listed instruments, Indian regulatory context).

**Platform:** Mobile-first (iOS and Android via Flutter). Web is out of scope for Version 1.

### Core Value Proposition

1. **Risk-free learning** — Trade with ₹10,00,000 virtual cash using real market dynamics.
2. **Motivation through gamification** — XP, levels, achievements, and challenges keep users engaged daily.
3. **Social proof and competition** — Leaderboards and leagues create community and retention.
4. **Progressive mastery** — Users grow from Student Trader to Market Legend through career titles, levels, and structured unlocks.
5. **Premium depth** — Optional subscription unlocks advanced analytics, exclusive challenges, and cosmetic/status rewards.

**One-line pitch:** *Learn to trade Indian stocks, compete with friends, and build your tycoon empire — all with zero financial risk.*

### Long-Term Vision

Paper Trading Tycoon aims to become **India's leading gamified investing education platform**, reaching 1 million active users within three years of launch.

**Phase 1 (V1):** Core paper trading + gamification loop (launch).  
**Phase 2 (V2–V3):** Social features, advanced analytics, educational content, tournaments.  
**Phase 4 (V4+):** Community-driven strategies, AI coaching, institutional partnerships, optional real-broker integrations (referral-only, not execution).

The north star metric is **Daily Active Users (DAU)** with secondary focus on **7-day and 30-day retention**, **trades per user per week**, and **premium conversion rate**.

---

## 2. User Journey

The following flow represents the complete end-to-end user lifecycle from first launch through daily return visits.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         FIRST APP LAUNCH                                     │
│  Splash → Onboarding slides (what is paper trading, gamification, safety)   │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         REGISTRATION / LOGIN                                 │
│  Options: Email+Password | Google | Apple (iOS)                             │
│  Email verification required before full access                              │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                    WELCOME & VIRTUAL CASH GRANT                              │
│  Celebration animation → ₹10,00,000 credited to virtual wallet             │
│  Tutorial overlay: "This is your paper trading capital"                      │
│  Starting Level 1, 0 XP, "Student Trader" title, Bronze League assigned      │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         EXPLORE STOCK MARKET                                 │
│  Browse: Trending | Gainers | Losers | Watchlist | Search                    │
│  View stock detail: price chart, day range, market cap, sector               │
│  Add stocks to watchlist                                                     │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         BUY STOCKS                                           │
│  Select stock → Enter quantity or amount → Preview order                     │
│  Confirm buy → Order executed at current market price                        │
│  Virtual cash debited → Holdings updated → Trade recorded in history         │
│  XP awarded for first trade (+ bonus for milestone trades)                   │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         PORTFOLIO MONITORING                                 │
│  Dashboard: total value, day P&L, overall P&L, allocation chart              │
│  Holdings list with live price updates and unrealized gain/loss              │
│  Performance graph over time (1D, 1W, 1M, 3M, ALL)                           │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         SELL STOCKS                                          │
│  Select holding → Enter quantity → Preview sell                              │
│  Confirm sell → Proceeds credited → Realized P&L calculated                  │
│  XP awarded → Achievement checks triggered                                   │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         PORTFOLIO GROWTH                                     │
│  Net worth tracked over time → Milestone notifications (₹12L, ₹15L, etc.)   │
│  Best/worst performers highlighted                                           │
│  Risk metrics surfaced at higher levels (Premium: advanced metrics)           │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         XP, LEVELS & CAREER TITLES                           │
│  Earn XP from: trades, daily login, challenges, achievements, referrals      │
│  Level up → Career title advances (Student Trader → Market Legend)          │
│  Unlocks: watchlist slots, analytics tabs, league eligibility, cosmetics      │
│  Level-up celebration screen with rewards (coins, badges, title reveal)      │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         CHALLENGES                                           │
│  Daily challenges: "Buy 3 different stocks", "Achieve 2% daily gain"         │
│  Weekly challenges: higher XP and coin rewards                               │
│  Challenge progress tracked in real time → Claim reward on completion        │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         ACHIEVEMENTS                                         │
│  Permanent badges: "First Trade", "Portfolio ₹20L", "10-Day Streak"          │
│  Hidden achievements for surprise delight                                  │
│  Achievement gallery on profile                                              │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         LEADERBOARDS                                         │
│  Rankings by: portfolio growth %, total XP, weekly P&L, league tier          │
│  Leagues: Bronze → Silver → Gold → Platinum → Diamond                        │
│  Weekly league reset with promotion/demotion                                 │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         COIN ECONOMY & STORE                                 │
│  Earn coins from challenges, achievements, daily login, level-ups            │
│  Spend coins in store: avatars, themes, profile frames, boosters             │
│  Boosters: temporary XP multipliers, extra challenge slots (non-pay-to-win)  │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         PREMIUM SUBSCRIPTION                                 │
│  Paywall surfaced after user demonstrates engagement (Level 5+ or Day 7+)    │
│  Benefits: advanced analytics, exclusive challenges, ad-free, priority support│
│  Monthly / Annual plans via App Store / Play Store in-app purchase           │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         REFERRAL (OPTIONAL)                                  │
│  Share referral code → Friend registers → Both earn coins + XP               │
│  Referral leaderboard for top inviters                                       │
└──────────────────────────────────┬──────────────────────────────────────────┘
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         DAILY RETURN LOOP                                    │
│  Push notification: market open, daily challenge, streak reminder            │
│  Daily login reward (coins + XP, streak multiplier)                          │
│  Check portfolio → Execute trades → Complete challenges → Check rank         │
│  Streak broken notification if missed → Re-engagement campaign                 │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Journey States Summary

| Stage | User State | System Actions |
|-------|-----------|----------------|
| Onboarding | Anonymous | Show value prop, no account required yet |
| Registered | Authenticated, verified | Create user, wallet, portfolio, assign league |
| Active Trader | Has ≥1 trade | Enable full gamification, leaderboard eligibility |
| Engaged | Level 5+, 7-day streak | Surface premium, advanced challenges |
| Premium | Subscribed | Unlock premium features, badge on profile |
| At Risk | 3+ days inactive | Send re-engagement notifications |
| Churned | 30+ days inactive | Win-back campaign, streak reset |

---

## 3. Feature Modules

Each module is a bounded context within the system. Modules communicate via the Laravel REST API on the client side and via **domain events** on the backend — modules must not call each other directly for cross-cutting reactions (XP, achievements, notifications). The Flutter app mirrors these modules under `lib/features/`.

---

### 3.1 Authentication

**Purpose:** Secure user identity, session management, and access control.

**Responsibilities:**
- User registration (email/password, Google OAuth, Apple Sign-In)
- Login, logout, token refresh (Sanctum)
- Email verification and password reset flows
- Device session tracking
- Rate limiting on auth endpoints
- Account deletion (GDPR-style data erasure request)
- Publish `UserRegistered` domain event on successful registration

**Dependencies:** User Profile (creates profile on registration via event), Notifications (verification emails)

**Future Expansion:** Two-factor authentication (2FA), biometric login passthrough, SSO for enterprise/education partners

---

### 3.2 User Profile

**Purpose:** Central identity and public-facing user representation.

**Responsibilities:**
- Display name, avatar, bio, level, XP, career title, league tier
- Profile customization (avatars, frames from store)
- Privacy settings (leaderboard visibility)
- Account settings linkage
- Public profile view for other users
- Statistics summary (total trades, win rate, best trade)

**Dependencies:** Authentication, Game Engine (career title, level, XP display), Store, Achievements

**Future Expansion:** Profile badges wall, trading style tags, verified trader status, career title showcase animations

---

### 3.3 Stock Market

**Purpose:** Present market data and enable stock discovery.

**Responsibilities:**
- Stock listing (NSE/BSE symbols)
- Real-time or delayed price quotes (based on provider tier)
- Stock search and filtering (sector, market cap, trending)
- Stock detail views (price, chart, fundamentals summary)
- Watchlist management (add/remove/reorder)
- Market status indicator (open, closed, pre-market)
- Corporate actions awareness (splits, dividends — informational only)

**Dependencies:** Market Data Service (quotes and symbol data), Authentication

**Future Expansion:** Sector heatmaps, screeners, news feed integration, options chain (V3+, feature-flagged)

---

### 3.4 Trading Engine

**Purpose:** Execute virtual buy/sell orders with market-realistic rules. The Trading Engine is responsible **only** for order validation and execution — it does not update portfolios, award XP, or notify users directly.

**Responsibilities:**
- Accept trade requests (buy/sell) with idempotency keys
- Pre-execution validation: read-only query of current cash and holdings from Portfolio before order acceptance
- Market order execution at price supplied by Market Data Service
- Order validation (sufficient cash, sufficient holdings, market hours)
- Order history and trade log persistence
- Transaction atomicity within the trading boundary (order record creation)
- Slippage simulation (optional, configurable via Rules Engine — disabled in V1)
- Trading fees simulation (optional — disabled in V1)
- Idempotency for duplicate submission protection
- Publish `TradeExecuted` domain event on successful execution — **never** call Portfolio, Game Engine, or Analytics directly

**Dependencies:** Market Data Service (current price), Portfolio (read-only cash/holdings check), Rules Engine (market hours, fee rules)

**Future Expansion:** Limit orders, stop-loss orders, basket trades, paper options (feature-flagged), intraday vs delivery modes

---

### 3.5 Portfolio

**Purpose:** Track user holdings, cash balance, and performance. Reacts to trading events — never invoked synchronously by the Trading Engine.

**Responsibilities:**
- Virtual cash wallet (starting balance ₹10,00,000)
- Holdings ledger (symbol, quantity, average buy price)
- Subscribe to `TradeExecuted` → update cash, holdings, and trade ledger atomically
- Publish `PortfolioUpdated` domain event after every mutation
- Real-time portfolio valuation (holdings × current price + cash)
- Realized and unrealized P&L calculation
- Portfolio performance history (snapshots for charts)
- Asset allocation breakdown
- Dividend simulation crediting (future)

**Dependencies:** Market Data Service (valuation prices), User Profile

**Future Expansion:** Multiple portfolios, portfolio sharing, benchmark comparison (Nifty 50)

---

### 3.6 Game Engine

**Purpose:** Central orchestration layer for all game mechanics. Paper Trading Tycoon is a game first — the Game Engine owns progression, rewards, and economy logic. Feature modules (Achievements, Challenges, Leaderboards) subscribe to Game Engine events rather than being called directly by the Trading Engine.

**Sub-Engines and Responsibilities:**

| Sub-Engine | Responsibility |
|------------|---------------|
| **XP Engine** | Calculate XP grants from rules; publish `XPGranted`; maintain XP audit log |
| **Career Progression** | Map levels to career titles; manage title unlocks and display metadata |
| **Level Engine** | Evaluate level thresholds; publish `LevelUp` with unlock payload |
| **League Engine** | Weekly league assignment, promotion, demotion; publish league change events |
| **Reward Engine** | Coordinate coin, XP, and item rewards; publish `CoinsAwarded`, `SeasonRewardGranted` |
| **Mission Engine** | Drive daily/weekly challenge templates; delegate progress to Challenge module |
| **Season Engine** | Manage seasonal cycles, season XP, and end-of-season rewards |
| **Battle Pass Engine** | Track tier progress and premium track rewards *(future, feature-flagged)* |
| **Economy Engine** | Interface to Coin Economy ledger; never mutate balances directly |
| **Event Dispatcher** | Publish and route all domain events to registered listeners via queue |

**Career Progression — Titles and Philosophy:**

Career titles replace raw level numbers as the primary identity signal. Levels remain the underlying mechanic; titles are the narrative layer that communicates mastery.

| Level Range | Career Title | Narrative Stage |
|-------------|-------------|-----------------|
| 1–5 | Student Trader | Learning fundamentals |
| 6–10 | Intern Trader | First consistent activity |
| 11–15 | Junior Trader | Building habits |
| 16–20 | Retail Trader | Competent independent trader |
| 21–30 | Professional Trader | Strong performance track record |
| 31–40 | Senior Trader | Advanced strategy and consistency |
| 41–50 | Fund Manager | Portfolio management mindset |
| 51–60 | Portfolio Manager | Multi-asset thinking |
| 61–75 | Hedge Fund Manager | Elite competitive tier |
| 76+ | Market Legend | Top-tier status symbol |

**Progression Philosophy:**
- Titles reward **consistent engagement**, not just lucky trades — XP comes from diverse actions (login, challenges, achievements, trades).
- Each title tier should feel like a promotion at work — visible status users want to share.
- Demotion applies to **leagues**, not career titles — titles are permanent milestones (level can only increase).

**Unlock Types:**

| Unlock Category | Examples |
|----------------|---------|
| **Feature Unlocks** | Watchlist slots (+2 at Intern), advanced chart intervals (Professional+), sector analytics (Senior+), premium challenge slots (Fund Manager+) |
| **Cosmetic Unlocks** | Title-specific profile badges, animated avatars, exclusive profile frames, title-colored name display on leaderboards |
| **Gameplay Unlocks** | Weekly tournament eligibility (Retail+), referral bonus multiplier (Professional+), exclusive season missions (Portfolio Manager+) |

**Why This Separation Improves Scalability:**
- Game logic is isolated from trading and portfolio logic — balance changes do not require touching the Trading Engine.
- New game features (seasons, battle pass) plug into the Game Engine without modifying existing modules.
- The Event Dispatcher enables async processing — heavy reward calculations run on queues, not in the trade request path.
- Rules Engine drives all thresholds — game balancing requires no code deployment.
- Game Engine can later be extracted to a dedicated service at 1M+ users without rewriting trading logic.

**Dependencies:** Rules Engine, Coin Economy, Feature Flags

**Future Expansion:** Battle pass, guild/team progression, seasonal narrative arcs, live ops event calendar

---

### 3.7 Achievements

**Purpose:** Permanent milestone badges rewarding long-term engagement.

**Responsibilities:**
- Achievement definition catalog (admin-managed via Rules Engine)
- Subscribe to domain events (`TradeExecuted`, `PortfolioUpdated`, `LevelUp`, `ChallengeCompleted`) — **event-driven only**
- Progress tracking per user per achievement
- Unlock detection on relevant events
- Publish `AchievementUnlocked` on unlock — Reward Engine handles coin/XP grants
- Achievement notification trigger via event
- Hidden achievements support
- Achievement gallery on profile

**Dependencies:** Game Engine (Reward Engine for grants), Notifications, User Profile

**Future Expansion:** Rare/legendary tiers, time-limited event achievements, NFT-style collectibles (cosmetic only)

---

### 3.8 Challenges

**Purpose:** Time-bound tasks driving daily and weekly engagement.

**Responsibilities:**
- Daily challenge generation (from Rules Engine template pool)
- Weekly challenge assignment via Mission Engine
- Subscribe to domain events for progress (`TradeExecuted`, `PortfolioUpdated`, `XPGranted`)
- Progress tracking (e.g., "buy 3 stocks" — increment on `TradeExecuted`)
- Publish `ChallengeCompleted` on completion — Reward Engine distributes XP and coins
- Challenge expiry and reset scheduling
- Premium-exclusive challenge pool (gated by Feature Flags + Premium module)

**Dependencies:** Game Engine (Mission Engine, Reward Engine), Coin Economy, Notifications

**Future Expansion:** User-created challenges, community voting on challenges, sponsored brand challenges

---

### 3.9 Leaderboards

**Purpose:** Competitive ranking across multiple dimensions.

**Responsibilities:**
- Subscribe to `PortfolioUpdated`, `XPGranted`, `LevelUp` for rank recalculation
- Global and league-scoped leaderboards
- Ranking dimensions: portfolio growth %, weekly P&L, total XP, referral count
- Periodic reset (weekly for P&L boards, never for all-time XP)
- Rank change notifications (entered top 10, promoted league)
- Anti-cheat integration: flag suspicious portfolio spikes for review
- Pagination and caching for performance

**Dependencies:** Portfolio (read-only snapshots), Game Engine (league tiers), User Profile, Anti-Cheat System

**Future Expansion:** Friend-only leaderboards, city/state regional boards, tournament brackets

---

### 3.10 Coin Economy

**Purpose:** Ledger-based soft currency system. Coin balances are **derived from transaction history**, never updated directly — this ensures auditability, fraud detection, and economic integrity at scale.

**Responsibilities:**

| Component | Responsibility |
|-----------|---------------|
| **Wallet** | One wallet per user; balance computed as sum of all ledger entries |
| **Coin Transactions** | Append-only ledger: every credit and debit is an immutable record |
| **Reward Sources** | Typed source on every credit: `challenge`, `achievement`, `level_up`, `referral`, `daily_login`, `season_reward`, `admin_grant` |
| **Audit Trail** | Full history queryable by user, source, and date — required for support and fraud review |
| **Fraud Protection** | Daily earn caps, duplicate source detection, velocity checks, integration with Anti-Cheat System |

**Ledger Principles:**
- **Never** update a balance column directly — always insert a transaction row; balance is computed or cached via materialized view refreshed on write.
- Every debit (store purchase) references the credit that funded it via transaction chain.
- Duplicate reward grants for the same source event are rejected via idempotency key (`source_type` + `source_id`).
- Negative balance is impossible — debit requests validate sufficient computed balance before insert.

**Why Ledger-Based:**
- Full audit trail for support ("why did my coins drop?") and dispute resolution.
- Fraud detection can replay transaction history to find anomalies.
- Economy rebalancing (refunds, rollbacks) is a compensating transaction, not a silent balance edit.
- Scales to millions of transactions without balance drift from race conditions.

**Dependencies:** Game Engine (Reward Engine publishes `CoinsAwarded`), Store, Anti-Cheat System

**Future Expansion:** Coin gifting, limited-time double-coin events, coin-to-premium-discount conversion

**Future Expansion:** Coin gifting, limited-time double-coin events, coin-to-premium-discount conversion

---

### 3.11 Store

**Purpose:** Virtual goods marketplace using coins (and premium currency in future).

**Responsibilities:**
- Catalog management (avatars, themes, profile frames, boosters)
- Purchase flow (deduct coins, grant item to inventory)
- Inventory management per user
- Equip/unequip cosmetic items
- Booster activation and expiry
- Limited-edition item drops

**Dependencies:** Coin Economy (ledger debit on purchase), User Profile

**Future Expansion:** Seasonal store rotations, bundle offers, premium-exclusive items

---

### 3.12 Premium

**Purpose:** Subscription monetization layer.

**Responsibilities:**
- Subscription plan definition (monthly, annual)
- In-app purchase verification (Apple App Store, Google Play)
- Subscription status sync and expiry handling
- Feature gating via Feature Flags module (premium-only features check)
- Publish `PremiumPurchased` domain event on successful subscription
- Grace period and renewal failure handling
- Premium badge on profile and leaderboard

**Dependencies:** User Profile, Authentication, Feature Flags, Analytics (conversion tracking)

**Future Expansion:** Family plans, student discounts, lifetime purchase option, tiered premium (Pro/Elite)

---

### 3.13 Notifications

**Purpose:** Re-engage users via push, email, and in-app alerts. **Subscriber-only module** — reacts to domain events, never called directly by business modules.

**Responsibilities:**
- Push notification dispatch (FCM for Android, APNs for iOS)
- Notification preference management per user
- Subscribe to all user-facing domain events and map to notification templates
- Trigger mapping: market open, challenge reminder, streak at risk, `AchievementUnlocked`, league change, `LevelUp`, `PremiumPurchased`, referral joined
- In-app notification inbox with read/unread state
- Email for transactional messages (verification, password reset)
- Quiet hours respect

**Dependencies:** Domain events (subscriber), external Notification Service

**Future Expansion:** SMS for critical alerts, WhatsApp integration (India market), rich push with action buttons

---

### 3.14 Referral System

**Purpose:** Organic growth through user invitations.

**Responsibilities:**
- Unique referral code generation per user
- Referral link creation and deep linking
- Attribution on registration (referrer tracking)
- Publish referral attribution event on registration; Reward Engine handles coin/XP grants
- Referral count and history
- Anti-fraud: same-device detection, self-referral block, reward cap — delegated to Anti-Cheat System

**Dependencies:** Authentication, Game Engine (Reward Engine), Anti-Cheat System, Notifications

**Future Expansion:** Tiered referral rewards, influencer/affiliate dashboard, referral contests

---

### 3.15 Settings

**Purpose:** User-controlled app configuration and account management.

**Responsibilities:**
- Notification preferences
- Display preferences (theme: light/dark/system)
- Privacy settings (profile visibility, leaderboard opt-out)
- Account management (change password, change email, delete account)
- App version and legal links (Terms, Privacy Policy)
- Language selection (English V1; Hindi V2)

**Dependencies:** Authentication, Notifications, User Profile

**Future Expansion:** Data export (GDPR), linked accounts management, trading preferences defaults

---

### 3.16 Admin Panel

**Purpose:** Internal operations, content management, and moderation.

**Responsibilities:**
- User management (view, suspend, ban, restore)
- Achievement and challenge template CRUD
- Store catalog management
- Leaderboard inspection and manual adjustment (with audit log)
- Stock symbol management (enable/disable instruments)
- System configuration delegated to Rules Engine and Feature Flags
- Anti-cheat review queue
- Analytics dashboard (DAU, retention, revenue)
- Push notification broadcast (marketing)

**Dependencies:** All backend modules, Rules Engine, Feature Flags, Analytics

**Future Expansion:** Role-based admin access (support, content, super-admin), automated moderation rules, A/B test configuration

---

### 3.17 Analytics

**Purpose:** Product intelligence, business metrics, and event tracking. **Subscriber-only module** — consumes domain events for server-side analytics without coupling producers to analytics logic.

**Responsibilities:**
- Client-side event tracking (screen views, button taps, funnel steps)
- Subscribe to all domain events for server-side logging (trades, registrations, purchases, level-ups)
- Key metric aggregation: DAU, MAU, retention cohorts, ARPU, LTV
- Funnel analysis: registration → first trade → day-7 retention → premium conversion
- Crash and error reporting integration
- Admin-facing dashboards

**Dependencies:** Domain events (subscriber)

**Future Expansion:** Real-time analytics pipeline, predictive churn model, A/B testing framework

---

### 3.18 Market Data Layer

**Purpose:** Isolate all external stock data access behind a dedicated layer. The Trading Engine and Portfolio modules **never** communicate directly with external providers.

**Layer Stack:**

```
External Stock Provider (Alpha Vantage, NSE vendor, etc.)
        ↓
Market Data Provider Adapter (normalizes provider response format)
        ↓
Caching Layer (Redis/file cache — TTL by data type)
        ↓
Market Data Service (single internal API for all consumers)
        ↓
Consumers: Trading Engine | Portfolio | Stock Market module
```

**Responsibilities:**

| Layer | Responsibility |
|-------|---------------|
| **External Stock Provider** | Third-party API for NSE/BSE quotes, historical OHLCV, symbol master |
| **Market Data Provider Adapter** | Normalize provider-specific formats; handle provider failover |
| **Caching Layer** | Quote cache (15–60s TTL market hours); symbol master cache (24h); historical cache (1h); single-flight pattern to prevent stampede |
| **Market Data Service** | Internal service exposing `getQuote(symbol)`, `getQuotes(symbols[])`, `getHistorical(symbol, range)`, `getMarketStatus()`, `searchSymbols(query)` |

**Rules:**
- Flutter app calls Laravel API only — never the external provider.
- Trading Engine calls `MarketDataService.getQuote()` — never the adapter or provider directly.
- Provider API keys exist only in backend configuration.
- Cache invalidation is time-based; manual bust via admin for symbol master updates.

**Dependencies:** External Stock Provider, Rules Engine (cache TTL configuration)

**Future Expansion:** WebSocket price stream adapter (V3), multi-provider load balancing, corporate actions feed

---

### 3.19 Rules Engine

**Purpose:** Externalize all game and economy balancing rules so that tuning requires **no code deployment**. Admin panel writes rules; runtime reads from database with config cache.

**Configurable Rule Categories:**

| Category | Examples |
|----------|---------|
| **XP Rewards** | XP per trade, daily login bonus, streak multiplier curve |
| **Coin Rewards** | Coins per challenge tier, achievement tier rewards, referral bonus amounts |
| **Challenge Difficulty** | Template pool weights, premium challenge parameters |
| **League Promotion** | Top N promote, bottom N demote, demotion protection rules |
| **Level Thresholds** | XP required per level, career title mapping |
| **Season Configuration** | Season duration, season XP multiplier, end-of-season reward tiers |
| **Trading Rules** | Market hours, starting cash amount, max order size |
| **Economy Caps** | Daily coin earn limit, max referral rewards per month |

**Implementation Pattern:**
- Rules stored as versioned JSON documents in database, keyed by rule set name.
- Laravel config cache refreshed on admin save (event-driven invalidation).
- All engines (XP, Level, League, Reward, Mission, Season) read rules at runtime — zero hardcoded thresholds in service classes.
- Rule changes are audit-logged with admin user ID and timestamp.

**Dependencies:** Admin Panel (rule management UI)

**Future Expansion:** A/B test rule variants per user cohort, scheduled rule activation (live ops calendar)

---

### 3.20 Feature Flags

**Purpose:** Enable or disable features at runtime without releasing a new app version. Supports gradual rollout, premium gating, and kill switches.

**Responsibilities:**
- Flag definition store (database-backed, admin-managed)
- Server-side flag evaluation API: `GET /api/v1/feature-flags`
- Client caches flags on app launch; refreshes on foreground
- User-level overrides (premium users, beta testers, percentage rollout)
- Kill switch capability for broken features in production

**V1 Flags (infrastructure only — most flags disabled at launch):**

| Flag Key | Default (V1) | Purpose |
|----------|-------------|---------|
| `crypto_trading` | off | Simulated crypto paper trading |
| `options_trading` | off | Simulated options paper trading |
| `battle_pass` | off | Seasonal battle pass progression |
| `ai_coach` | off | AI-powered trade suggestions |
| `copy_trading` | off | Copy another user's paper trades |
| `tournaments` | off | Weekly trading tournaments |
| `advanced_analytics` | premium | Sharpe ratio, drawdown charts |

**How It Works Without App Updates:**
- Flutter checks flag before rendering feature entry points — hidden UI if flag is off.
- API returns 404 or 403 for disabled feature endpoints — defense in depth.
- New features ship dormant in app bundle; flag enables them server-side when ready.
- Percentage rollout: hash user ID to deterministically include/exclude users.

**Dependencies:** Admin Panel, Premium (for premium-gated flags)

**Future Expansion:** Geo-based flags, cohort targeting, flag analytics (exposure tracking)

---

### 3.21 Anti-Cheat System

**Purpose:** Detect and mitigate abuse across trading, gamification, referrals, and leaderboards. Operates as an event subscriber and request interceptor — not embedded in individual modules.

**Responsibilities:**
- Idempotency key validation on trade requests (duplicate request rejection)
- Rapid trading detection (velocity limits per user per minute)
- Referral abuse detection (same device, same IP cluster, self-referral, circular referrals)
- XP farming detection (diminishing returns, daily caps, action diversity scoring)
- Leaderboard manipulation detection (abnormal portfolio spikes, synchronized trading patterns)
- Flag suspicious accounts for admin review queue
- Automatic temporary suspension for high-confidence abuse

**Dependencies:** Domain events (subscriber), Admin Panel (review queue), Rules Engine (threshold configuration)

**Future Expansion:** ML-based anomaly detection, device fingerprinting service, behavioral scoring model

---

### Module Dependency Graph (Event-Driven)

Modules do **not** call each other directly for reactions. Solid lines = data/API dependency. Dashed lines = domain event subscription.

```
Authentication ──► User Profile ──► Settings
       │ publish              ▲
       │ UserRegistered       │ reads career title, level
       ▼                      │
Market Data Layer ──► Stock Market (browse/search)
       │
       │ getQuote()
       ▼
Trading Engine ──publish──► TradeExecuted ──subscribe──► Portfolio
                               │                              │ publish
                               │                              ▼
                               │                      PortfolioUpdated
                               │                              │
                               └──────────────┬───────────────┘
                                              ▼
                                        Game Engine
                                    (XP, Level, League,
                                     Reward, Mission,
                                     Season, Economy)
                                              │ publish
                         ┌────────────────────┼────────────────────┐
                         ▼                    ▼                    ▼
                   XPGranted            LevelUp            CoinsAwarded
                         │                    │                    │
              ┌──────────┴────────┐          │          ┌─────────┴────────┐
              ▼                   ▼          ▼          ▼                  ▼
        Challenge Engine    Achievement  Leaderboard  Coin Economy      Store
              │               Engine       Engine      (ledger)
              │ publish           │ publish
              ▼                   ▼
      ChallengeCompleted   AchievementUnlocked
              │                   │
              └─────────┬─────────┘
                        ▼
              ┌─────────────────────────┐
              │  Notifications          │
              │  Analytics              │  ← subscribe to ALL domain events
              │  Anti-Cheat System      │
              └─────────────────────────┘

Rules Engine ──configures──► Game Engine, Trading Engine, Anti-Cheat
Feature Flags ──gates──► Premium, Store, future features
Referral System ──publish──► UserRegistered (with referral attribution)
Premium ──publish──► PremiumPurchased
Admin Panel ──manages──► Rules Engine, Feature Flags, all modules
```

---

## 4. System Architecture

### Architecture Overview

```
┌──────────────────────────────────────────────────────────────────┐
│                        FLUTTER MOBILE APP                         │
│  Presentation → Domain → Data (Clean Architecture)              │
│  Riverpod | Go Router | Dio | Hive | Feature Flag Cache           │
└────────────────────────────┬─────────────────────────────────────┘
                             │ HTTPS / JSON REST
                             ▼
┌──────────────────────────────────────────────────────────────────┐
│                     LARAVEL REST API                              │
│  Controllers → Requests → Services → Repositories → Models      │
│  Sanctum Auth | Rate Limiting | Queue Workers                   │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │  Domain Event Bus (Laravel Events → Queued Listeners)      │ │
│  │  Game Engine | Rules Engine | Feature Flags | Anti-Cheat   │ │
│  └────────────────────────────────────────────────────────────┘ │
└────────────┬─────────────────────────────┬───────────────────────┘
             │                             │
             ▼                             ▼
┌────────────────────────┐    ┌────────────────────────────────────┐
│         MySQL          │    │       NOTIFICATION SERVICE         │
│  Primary data store    │    │  FCM (Android) + APNs (iOS)        │
│  + coin ledger         │    │  Email (transactional)             │
│  + rules config        │    └────────────────────────────────────┘
└────────────────────────┘
             │
             ▼
┌──────────────────────────────────────────────────────────────────┐
│                     MARKET DATA LAYER                             │
│  External Provider → Adapter → Cache → Market Data Service       │
└──────────────────────────────────────────────────────────────────┘
```

---

### Layer Responsibilities

#### Flutter App (Client)

| Concern | Responsibility |
|---------|---------------|
| **Presentation** | Screens, widgets, Riverpod providers/notifiers, UI state |
| **Domain** | Entities, repository interfaces, use cases (pure business rules) |
| **Data** | Repository implementations, remote/local data sources, DTO models |
| **Core** | Constants, themes, error types, utilities, extensions |
| **Services** | Dio HTTP client, Hive storage, dependency injection setup |
| **Routes** | Go Router configuration, route guards (auth required) |

The Flutter app is **stateless regarding business truth** — all authoritative data lives on the server. Hive caches non-sensitive read data (stock list, user preferences) for offline resilience. Trading actions require network connectivity.

#### Laravel REST API (Server)

| Concern | Responsibility |
|---------|---------------|
| **HTTP Layer** | Controllers, Form Request validation, API Resources (response shaping), Middleware |
| **Service Layer** | Business logic orchestration (trade execution, XP calculation, challenge progress) |
| **Repository Layer** | Data access abstraction over Eloquent |
| **Action Classes** | Single-responsibility command objects (e.g., `ExecuteBuyOrder`) |
| **Models** | Eloquent ORM entities |
| **Jobs/Queues** | Async domain event listeners: portfolio updates, game engine, notifications, leaderboard recalculation |
| **Events/Listeners** | Domain Event Bus — modules publish events; listeners subscribe asynchronously via queue |
| **Game Engine** | Central game mechanics orchestration (XP, levels, leagues, rewards, seasons) |
| **Rules Engine** | Runtime-configurable game and economy rules — no hardcoded thresholds |
| **Feature Flags** | Runtime feature gating without app releases |
| **Anti-Cheat** | Cross-cutting abuse detection on events and requests |

The API is the **single source of truth** for all user state, balances, and gamification progress.

#### MySQL (Database)

| Concern | Responsibility |
|---------|---------------|
| **Transactional data** | Users, portfolios, holdings, trades, orders |
| **Gamification data** | XP logs, levels, achievements, challenges, leaderboard snapshots |
| **Economy data** | Coin transaction ledger (append-only), store purchases, inventory |
| **Configuration** | Achievement definitions, challenge templates, store catalog, rules engine config, feature flags |
| **Audit** | Admin actions, anti-cheat flags, referral attribution, coin transaction history |

All tables include `id`, `created_at`, `updated_at` per project rules. Indexing strategy must support leaderboard queries and portfolio valuation at scale.

#### Market Data Layer

See Section 3.18 for full layer stack. Summary:

| Concern | Responsibility |
|---------|---------------|
| **Provider Adapter** | Normalize external API responses; failover between providers |
| **Caching Layer** | TTL-based quote, symbol, and historical caches; single-flight deduplication |
| **Market Data Service** | Single internal interface — the only entry point for Trading Engine, Portfolio, and Stock Market module |

The Trading Engine **never** calls external providers directly. All price requests go through `MarketDataService`.

**Candidate providers:** Alpha Vantage, Yahoo Finance (unofficial), NSE official data vendors, or Indian fintech data APIs. Final selection during Milestone 3 based on cost, reliability, and NSE/BSE coverage. ADR required before integration.

#### Notification Service (External)

| Concern | Responsibility |
|---------|---------------|
| **Push delivery** | Firebase Cloud Messaging (FCM) for cross-platform |
| **Email delivery** | Transactional email (Postmark, Amazon SES, or Mailgun) |
| **Device token management** | Store FCM tokens per user device |

Laravel dispatches notifications via queue jobs. Failures retry with exponential backoff. Users control preferences in Settings.

---

### Domain Event Architecture

The backend is **event-driven**. Modules publish domain events after completing their own responsibility. Other modules react by subscribing — **never by direct service calls across bounded contexts**.

**Core Domain Events:**

| Event | Publisher | Payload Summary |
|-------|-----------|----------------|
| `UserRegistered` | Authentication | user_id, referral_code, timestamp |
| `TradeExecuted` | Trading Engine | user_id, symbol, side, quantity, price, trade_id |
| `PortfolioUpdated` | Portfolio | user_id, total_value, cash, holdings_count |
| `XPGranted` | Game Engine (XP Engine) | user_id, amount, source, source_id |
| `LevelUp` | Game Engine (Level Engine) | user_id, new_level, career_title, unlocks[] |
| `ChallengeCompleted` | Challenges | user_id, challenge_id, reward_summary |
| `AchievementUnlocked` | Achievements | user_id, achievement_id, tier |
| `CoinsAwarded` | Game Engine (Reward Engine) | user_id, amount, source, source_id |
| `SeasonRewardGranted` | Game Engine (Season Engine) | user_id, season_id, reward_tier |
| `PremiumPurchased` | Premium | user_id, plan, expiry |

**Event Subscriber Matrix:**

| Event | Subscribers |
|-------|------------|
| `UserRegistered` | Portfolio (create wallet), Game Engine (initial XP/league), Referral System, Analytics, Notifications |
| `TradeExecuted` | Portfolio, Anti-Cheat, Analytics |
| `PortfolioUpdated` | Game Engine, Challenges, Achievements, Leaderboards, Analytics |
| `XPGranted` | Challenges, Analytics, Anti-Cheat |
| `LevelUp` | Achievements, Notifications, Analytics, User Profile (cache invalidation) |
| `ChallengeCompleted` | Game Engine (Reward Engine), Achievements, Notifications, Analytics |
| `AchievementUnlocked` | Game Engine (Reward Engine), Notifications, Analytics |
| `CoinsAwarded` | Coin Economy (ledger insert), Analytics, Anti-Cheat |
| `SeasonRewardGranted` | Coin Economy, Notifications, Analytics |
| `PremiumPurchased` | User Profile, Feature Flags, Notifications, Analytics |

**Event Delivery Rules:**
- All cross-module reactions use **queued listeners** — not synchronous calls — to keep the trade request path fast (< 500ms p95).
- Listeners must be **idempotent** — safe to retry on queue failure (use `source_id` deduplication).
- Event ordering per user is guaranteed within a single queue partition (user_id as queue key).
- Failed listeners log to dead letter queue; admin alerted on repeated failures.

---

### Request Flow Example: Buy Stock (Event-Driven)

```
User taps "Confirm Buy"
    → Flutter: BuyStockUseCase validates locally (quantity > 0)
    → Flutter: POST /api/v1/trades/buy { symbol, quantity, idempotency_key }
    → Laravel: Sanctum auth middleware
    → Laravel: Anti-Cheat validates idempotency + velocity
    → Laravel: BuyTradeRequest validation
    → Laravel: TradingEngine.executeBuy()
        → MarketDataService.getQuote(symbol)        ← never external provider directly
        → Validate market hours (Rules Engine)
        → Validate order rules
        → Persist trade record
        → Publish TradeExecuted event
    → Return TradeResource JSON immediately          ← user sees success fast

    ── Async (Queued Listeners) ──
    TradeExecuted listener → Portfolio Module
        → Update cash + holdings atomically
        → Publish PortfolioUpdated event

    PortfolioUpdated listener → Game Engine
        → XP Engine: calculate XP → publish XPGranted
        → Check level threshold → publish LevelUp (if applicable)

    PortfolioUpdated listener → Challenge Engine
        → Update challenge progress → publish ChallengeCompleted (if done)

    PortfolioUpdated listener → Achievement Engine
        → Check unlock conditions → publish AchievementUnlocked (if met)

    XPGranted / LevelUp / ChallengeCompleted / AchievementUnlocked
        → Game Engine Reward Engine → publish CoinsAwarded (if applicable)

    CoinsAwarded listener → Coin Economy
        → Insert ledger transaction (append-only, never balance update)

    All events → Leaderboard Engine (recalculate rank)
    All events → Analytics (log)
    LevelUp / AchievementUnlocked / ChallengeCompleted → Notifications

    → Flutter: Receives trade response; polls or receives push for XP/coin updates
```

**Benefits of This Flow:**
- **Loose coupling** — Trading Engine knows nothing about XP, achievements, or notifications.
- **Fast response** — User gets trade confirmation immediately; gamification runs async.
- **Independent scaling** — Game Engine listeners can scale on separate queue workers.
- **Safe retries** — Idempotent listeners recover from queue failures without duplicate rewards.
- **Easy extension** — Adding Battle Pass means subscribing to existing events, not modifying Trading Engine.
- **Testability** — Each listener tested in isolation with event fixtures.

---

### Deployment Architecture (V1)

| Component | Hosting |
|-----------|---------|
| Laravel API | Hostinger Shared Hosting (PHP 8.3+) |
| MySQL | Hostinger MySQL |
| Queue Worker | Hostinger cron-triggered queue worker |
| Flutter App | App Store + Google Play |
| Stock Data | External SaaS API |
| Push Notifications | Firebase (free tier) |
| File Storage | Hostinger local / future S3 migration |

**Scaling note:** Hostinger shared hosting is acceptable for V1 launch (target: 10K users). Migration to VPS/cloud (DigitalOcean, AWS, or Laravel Forge) is planned before 100K users. See Section 8 (Risks) and Future Scalability below.

---

### Future Scalability Path

The event-driven, layered architecture is designed to evolve incrementally — **no major rewrites** required at each growth tier.

#### 100,000 Users

| Component | Evolution |
|-----------|----------|
| **Hosting** | Migrate Laravel from Hostinger shared to VPS (DigitalOcean/Laravel Forge) |
| **Database** | Add read replica for leaderboard and analytics queries; optimize indexes on coin ledger and holdings |
| **Cache** | Introduce Redis for quote cache, feature flags, and session store |
| **Queues** | Dedicated queue worker process; separate queues for `trades`, `game`, `notifications` |
| **Market Data** | Redis cache layer with single-flight; bulk quote endpoint for portfolio valuation |
| **Architecture** | Remains monolithic Laravel — event bus is Laravel Events + Redis queue |

#### 1,000,000 Users

| Component | Evolution |
|-----------|----------|
| **Game Engine** | Extract to independent **Game Service** (still event-driven via Redis/RabbitMQ); owns XP, levels, leagues, seasons, rewards |
| **Leaderboard Engine** | Extract to independent service with pre-computed ranking tables; Redis sorted sets |
| **Market Data Layer** | Extract to independent **Market Data Service** with dedicated cache cluster |
| **Database** | Managed MySQL (RDS/PlanetScale); coin ledger partitioned by user_id hash |
| **Notifications** | Dedicated notification worker fleet; batch FCM sending |
| **Analytics** | Event stream to data warehouse (ClickHouse/BigQuery) via Kafka or Redis Streams |
| **API** | Laravel API remains core; game and leaderboard services called via internal HTTP or message bus |
| **Flutter** | No changes required — same REST API contract |

**Components Most Likely to Become Independent Services:**
1. **Game Engine** — highest write volume from XP/coin events; benefits most from isolation
2. **Leaderboard Engine** — heavy read/compute; benefits from Redis and dedicated scaling
3. **Market Data Layer** — provider rate limits and cache strategy need independent lifecycle
4. **Notification Service** — burst traffic at market open; isolate from API request path
5. **Analytics Pipeline** — already event-subscriber; natural candidate for stream processing

#### 10,000,000 Users

| Component | Evolution |
|-----------|----------|
| **API Gateway** | Kong or AWS API Gateway with rate limiting, auth, routing to services |
| **Trading Engine** | Extract to dedicated service with event sourcing for trade log integrity |
| **Database** | Shard MySQL by user_id; coin ledger and trades on dedicated shards |
| **Event Bus** | Migrate from Laravel Events to Apache Kafka or AWS SNS/SQS for cross-service events |
| **CDN** | CloudFront/Cloudflare for static assets and cached stock symbol master |
| **Flutter** | Still no rewrite — API gateway abstracts backend topology |
| **Admin Panel** | Separate admin SPA (Filament or React) with service-level admin APIs |

**Key Principle:** The domain event contracts (`TradeExecuted`, `LevelUp`, etc.) are the stable interface between components. Extracting a module to a service means moving its listener/producer logic — not changing event shapes. This is why event-driven architecture is the foundation for scale.

---

## 5. Folder Structure

### 5.1 Flutter (`lib/`)

```
lib/
├── main.dart                          # App entry point, ProviderScope initialization
├── app.dart                           # MaterialApp, theme, router attachment
│
├── core/
│   ├── constants/
│   │   ├── api_constants.dart         # Base URL, endpoint paths, timeouts
│   │   ├── app_constants.dart         # Starting cash, app name, version
│   │   └── storage_keys.dart          # Hive box and key names
│   ├── errors/
│   │   ├── exceptions.dart            # Data layer exceptions
│   │   └── failures.dart              # Domain layer failure types
│   ├── extensions/
│   │   ├── context_extensions.dart
│   │   ├── datetime_extensions.dart
│   │   └── num_extensions.dart        # Currency formatting (INR)
│   ├── theme/
│   │   ├── app_theme.dart
│   │   ├── app_colors.dart
│   │   └── app_text_styles.dart
│   └── utils/
│       ├── validators.dart
│       ├── formatters.dart            # Price, percentage, large number formatting
│       └── logger.dart
│
├── features/
│   ├── auth/
│   │   ├── data/
│   │   │   ├── datasources/
│   │   │   ├── models/
│   │   │   └── repositories/
│   │   ├── domain/
│   │   │   ├── entities/
│   │   │   ├── repositories/
│   │   │   └── usecases/
│   │   └── presentation/
│   │       ├── providers/
│   │       ├── screens/
│   │       └── widgets/
│   ├── onboarding/
│   ├── home/
│   ├── stock_market/
│   ├── trading/
│   ├── portfolio/
│   ├── gamification/                  # UI for XP, levels, career titles, leagues
│   ├── achievements/
│   ├── challenges/
│   ├── leaderboards/
│   ├── store/
│   ├── premium/
│   ├── notifications/
│   ├── referral/
│   ├── profile/
│   └── settings/
│
├── shared/
│   ├── widgets/                       # Reusable UI components
│   │   ├── buttons/
│   │   ├── cards/
│   │   ├── loaders/
│   │   └── dialogs/
│   ├── models/                        # Cross-feature models
│   └── mixins/
│
├── routes/
│   ├── app_router.dart                # GoRouter configuration
│   ├── route_names.dart               # Named route constants
│   └── route_guards.dart              # Auth guards
│
└── services/
    ├── api/
    │   ├── dio_client.dart            # Dio instance, interceptors
    │   ├── auth_interceptor.dart      # Token injection
    │   └── error_interceptor.dart     # Global error mapping
    ├── storage/
    │   └── hive_service.dart          # Local cache service
    ├── notification/
    │   └── push_service.dart          # FCM token registration
    ├── analytics/
    │   └── analytics_service.dart     # Event tracking wrapper
    └── feature_flags/
        └── feature_flag_service.dart  # Server flag cache and evaluation
```

### 5.2 Laravel (`/`)

```
/
├── app/
│   ├── Actions/                       # Single-purpose action classes
│   │   ├── Trade/
│   │   ├── Game/
│   │   └── User/
│   ├── Events/                        # Domain events (TradeExecuted, LevelUp, etc.)
│   ├── Listeners/                     # Queued event subscribers per module
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/                # Versioned API controllers
│   │   ├── Middleware/
│   │   ├── Requests/                  # Form request validation
│   │   └── Resources/                 # API response transformers
│   ├── Jobs/                          # Queue jobs
│   ├── Models/
│   ├── Repositories/
│   │   ├── Contracts/                 # Repository interfaces
│   │   └── Eloquent/                  # Eloquent implementations
│   ├── Services/
│   │   ├── Trading/
│   │   │   └── TradingEngine.php
│   │   ├── Portfolio/
│   │   │   └── PortfolioService.php
│   │   ├── Game/                      # Game Engine sub-services
│   │   │   ├── XPEngine.php
│   │   │   ├── LevelEngine.php
│   │   │   ├── LeagueEngine.php
│   │   │   ├── RewardEngine.php
│   │   │   ├── MissionEngine.php
│   │   │   ├── SeasonEngine.php
│   │   │   └── EventDispatcher.php
│   │   ├── MarketData/
│   │   │   ├── MarketDataService.php
│   │   │   ├── ProviderAdapter.php
│   │   │   └── QuoteCache.php
│   │   ├── Economy/
│   │   │   └── CoinLedgerService.php
│   │   ├── Rules/
│   │   │   └── RulesEngine.php
│   │   ├── Features/
│   │   │   └── FeatureFlagService.php
│   │   ├── Security/
│   │   │   └── AntiCheatService.php
│   │   └── NotificationService.php
│   └── Enums/                         # PHP 8.1+ enums (OrderType, LeagueTier, CareerTitle, etc.)
│
├── bootstrap/
├── config/
│   ├── trading.php                    # Starting cash, market hours (defaults — Rules Engine overrides)
│   ├── gamification.php               # Default XP weights (Rules Engine overrides at runtime)
│   ├── market_data.php                # Provider config, cache TTL defaults
│   └── feature_flags.php              # Flag registry and defaults
│
├── database/
│   ├── factories/
│   └── seeders/
│
├── routes/
│   ├── api.php                        # API route definitions
│   └── web.php                        # Admin panel routes
│
├── resources/
│   └── views/                         # Admin panel Blade views (V1 admin)
│
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
│
└── tests/
    ├── Feature/                       # API integration tests
    └── Unit/                          # Service and action unit tests
```

### 5.3 Documentation (`docs/`)

```
docs/
├── 00_MASTER_ARCHITECTURE.md          # This document
├── 01_API_SPECIFICATION.md            # Endpoint contracts (future)
├── 02_DATABASE.md                     # Schema design (future)
├── 03_GAMIFICATION_DESIGN.md          # Career titles, XP, levels, leagues rules (future)
├── 04_MARKET_DATA_INTEGRATION.md      # Market Data Layer integration guide (future)
├── 05_DOMAIN_EVENTS.md                # Event catalog, subscriber matrix, idempotency rules (future)
├── 06_DEPLOYMENT.md                   # Hosting and CI/CD (future)
├── 07_ADMIN_PANEL.md                  # Admin operations guide (future)
├── 08_RULES_ENGINE.md                 # Rule schema and admin configuration (future)
└── adr/                               # Architecture Decision Records
    ├── 001-tech-stack.md
    ├── 002-market-data-provider.md
    ├── 003-domain-event-bus.md
    ├── 004-coin-ledger-model.md
    └── ...
```

### 5.4 Assets

```
assets/
├── images/
│   ├── logo/
│   ├── onboarding/
│   ├── achievements/
│   └── store/
├── icons/
├── animations/                        # Lottie JSON files
│   ├── level_up.json
│   ├── coin_reward.json
│   └── confetti.json
└── fonts/
```

Flutter `pubspec.yaml` registers all asset paths. Images follow `@2x` and `@3x` resolution variants where applicable.

### 5.5 Configuration

```
# Flutter
.env.development                       # API base URL (dev)
.env.staging                           # Staging API URL
.env.production                        # Production API URL (not committed)

# Laravel
.env                                   # Database, API keys, mail, FCM (not committed)
.env.example                           # Template with placeholder keys

# CI/CD
.github/
├── workflows/
│   ├── flutter-ci.yml                 # Analyze, test, build
│   └── laravel-ci.yml                 # PHPUnit, PHPStan
```

**Rule:** Secrets never committed to version control. `.env.example` documents all required keys.

### 5.6 Scripts

```
scripts/
├── setup_dev.sh                       # Local dev environment bootstrap
├── seed_stocks.sh                     # Populate stock symbol master
├── recalculate_leaderboards.sh        # Manual leaderboard refresh
└── deploy.sh                          # Production deployment steps
```

### 5.7 Testing

```
# Flutter
test/
├── unit/                              # Use case and utility tests
├── widget/                            # Widget tests
└── integration/                       # End-to-end flow tests

# Laravel
tests/
├── Feature/
│   ├── Auth/
│   ├── Trading/
│   ├── Portfolio/
│   └── Gamification/
└── Unit/
    ├── Services/
    └── Actions/
```

---

## 6. Coding Standards

### 6.1 Naming Conventions

| Context | Convention | Example |
|---------|-----------|---------|
| Dart classes | PascalCase | `PortfolioRepository` |
| Dart variables, functions | camelCase | `totalPortfolioValue` |
| Dart files | snake_case | `portfolio_repository.dart` |
| Dart constants | camelCase or SCREAMING_SNAKE | `startingCash` or `API_BASE_URL` |
| PHP classes | PascalCase | `TradingService` |
| PHP methods | camelCase | `executeBuyOrder` |
| PHP variables | camelCase | `$portfolioValue` |
| Database tables | snake_case, plural | `user_achievements` |
| Database columns | snake_case | `average_buy_price` |
| API endpoints | kebab-case, plural nouns | `/api/v1/stock-quotes` |
| API JSON fields | snake_case | `{ "total_value": 1500000 }` |
| Environment variables | SCREAMING_SNAKE | `STOCK_DATA_API_KEY` |

### 6.2 Folder Conventions

- **One feature per folder** under `lib/features/` — self-contained with data/domain/presentation layers.
- **No cross-feature imports in data layer** — shared code goes in `lib/shared/` or `lib/core/`.
- **Laravel Actions** for single operations; **Services** for orchestration across repositories.
- **API version prefix** on all endpoints: `/api/v1/`.
- **Admin routes** separated under `/admin/` with distinct middleware.

### 6.3 File Conventions

- Every generated file includes a **header comment** stating its purpose and module ownership.
- One public class per file (Dart and PHP).
- Repository interface in domain layer; implementation in data layer (Flutter) or `Repositories/Eloquent/` (Laravel).
- API Resources (Laravel) one per response shape — no array returns from controllers.
- Widget files: suffix with `_screen.dart`, `_widget.dart`, or `_dialog.dart` for clarity.

### 6.4 Commenting Standards

- **Do comment:** Non-obvious business rules, complex algorithms (P&L calculation, league promotion logic), workarounds with ticket references.
- **Do not comment:** Self-explanatory code, getters/setters, boilerplate.
- **TODO format:** `// TODO(PTT-123): description` — must reference a ticket.
- **Deprecated format:** `@deprecated Use NewClass instead. Removal in v2.0.`

### 6.5 Error Handling Standards

**Flutter:**
- Data layer throws typed exceptions (`ServerException`, `NetworkException`).
- Repository catches exceptions and returns `Either<Failure, T>` or throws domain `Failure` types.
- Presentation layer maps failures to user-friendly messages via error mapper utility.
- Never show raw exception messages to users.

**Laravel:**
- Services throw domain-specific exceptions (`InsufficientFundsException`, `MarketClosedException`).
- Controllers catch and return appropriate HTTP status codes.
- Global exception handler logs 500 errors with stack trace; returns generic message to client.
- Validation errors always return 422 with field-level error messages.

**HTTP Status Code Mapping:**

| Status | Usage |
|--------|-------|
| 200 | Successful GET, PUT, PATCH |
| 201 | Successful POST (resource created) |
| 204 | Successful DELETE |
| 400 | Malformed request |
| 401 | Unauthenticated |
| 403 | Unauthorized (authenticated but not permitted) |
| 404 | Resource not found |
| 422 | Validation failure |
| 429 | Rate limit exceeded |
| 500 | Internal server error |

### 6.6 API Response Standards

**Success Response (single resource):**
```
{
  "success": true,
  "data": { ... },
  "message": "Optional human-readable message"
}
```

**Success Response (collection with pagination):**
```
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8
  }
}
```

**Error Response:**
```
{
  "success": false,
  "message": "Human-readable error summary",
  "errors": {
    "field_name": ["Specific validation error"]
  }
}
```

**Rules:**
- All API responses follow this envelope — no bare arrays or objects at root.
- Pagination uses Laravel's standard pagination meta.
- Timestamps in ISO 8601 UTC format.
- Monetary values as integers (paise) or strings to avoid floating-point issues — decision documented in ADR before implementation.

### 6.7 Logging Standards

**Laravel:**
- Use structured logging (JSON format in production).
- Log levels: `error` for failures requiring attention, `warning` for recoverable issues, `info` for business events (trade executed, user registered), `debug` for development only.
- Never log passwords, tokens, or PII beyond user ID.
- Include `request_id` in all log entries for traceability.

**Flutter:**
- Debug logs via `logger` package — disabled in production builds.
- Crash reporting via Firebase Crashlytics for uncaught exceptions.
- Analytics events for user actions (separate from debug logs).

### 6.8 Validation Standards

- **All input validated at API boundary** using Laravel Form Requests — never trust client data.
- **Business rule validation in Service layer** (e.g., sufficient funds — requires DB read).
- **Flutter validates for UX only** (immediate feedback) — server is authoritative.
- Validation messages are user-friendly and actionable.
- Sanitize all string inputs to prevent XSS in admin panel.

---

## 7. Development Roadmap

### Milestone 1 — Project Foundation
**Target:** Week 1–2

- Initialize Flutter project with folder structure, theme, routing skeleton
- Initialize Laravel project with folder structure, Sanctum, API versioning, domain event scaffold
- Implement Rules Engine infrastructure and Feature Flags infrastructure (disabled by default)
- Configure development environments and `.env` templates
- Set up CI pipelines (analyze, lint, test)
- Create documentation scaffold (`docs/` folder, ADR template)
- Deploy empty API to Hostinger staging

**Exit Criteria:** Both projects build successfully; staging API returns health check endpoint; CI green.

---

### Milestone 2 — Authentication & User Profile
**Target:** Week 3–4

- Registration, login, logout, token refresh
- Email verification and password reset
- Google and Apple social login
- User profile CRUD with career title display (Student Trader at registration)
- Publish `UserRegistered` event → Portfolio wallet creation, Game Engine initial league assignment
- Flutter auth flow with secure token storage
- Route guards (authenticated vs guest)

**Exit Criteria:** User can register, verify email, log in, view/edit profile; tokens persist across app restarts.

---

### Milestone 3 — Market Data Layer
**Target:** Week 5–6

- Implement Market Data Layer (Provider Adapter → Cache → Market Data Service)
- Integrate external stock data provider via adapter (never direct from consumers)
- Stock symbol master seeded with NSE/BSE top stocks
- Stock listing, search, detail endpoints
- Watchlist CRUD
- Flutter stock market screens (list, detail, search, watchlist)
- Market status indicator

**Exit Criteria:** User can browse, search, and view stock details with live/delayed prices; watchlist persists.

---

### Milestone 4 — Trading Engine & Portfolio
**Target:** Week 7–9

- Virtual wallet with ₹10,00,000 starting balance on `UserRegistered` event
- Trading Engine: buy/sell with idempotency keys; publish `TradeExecuted` only
- Portfolio: subscribe to `TradeExecuted`; publish `PortfolioUpdated`
- Anti-Cheat: idempotency validation and velocity limits on trade endpoints
- Holdings and cash ledger
- Portfolio valuation and P&L calculation
- Trade history
- Flutter trading flow (buy/sell confirmation, order success)
- Flutter portfolio dashboard

**Exit Criteria:** User can buy/sell stocks; portfolio reflects accurate holdings, cash, and P&L; trade history visible.

---

### Milestone 5 — Game Engine Core
**Target:** Week 10–11

- Game Engine: XP Engine, Level Engine, Career Progression titles, League Engine
- Rules Engine: XP weights, level thresholds, career title mapping (admin-configurable)
- Subscribe to `PortfolioUpdated`, `UserRegistered`; publish `XPGranted`, `LevelUp`
- Daily login streak and rewards via Reward Engine
- Flutter XP bar, career title display, level-up animation

**Exit Criteria:** User earns XP from trades and login; levels increase correctly; league displayed on profile.

---

### Milestone 6 — Achievements & Challenges
**Target:** Week 12–13

- Achievement catalog and progress tracking
- Achievement unlock via domain event subscriptions; publish `AchievementUnlocked`
- Daily and weekly challenges via Mission Engine; publish `ChallengeCompleted`
- Challenge progress and reward claim
- Flutter achievements gallery and challenges screen

**Exit Criteria:** Achievements unlock on milestones; daily challenge refreshes; rewards claimable.

---

### Milestone 7 — Leaderboards
**Target:** Week 14

- Leaderboard calculation (portfolio growth, XP, weekly P&L)
- League-scoped boards
- Weekly reset job
- Flutter leaderboard screens with rank display

**Exit Criteria:** Leaderboards display accurate rankings; weekly reset works; user sees own rank.

---

### Milestone 8 — Coin Economy & Store
**Target:** Week 15–16

- Ledger-based coin economy (append-only transactions, computed balance — never direct balance update)
- Reward Engine → `CoinsAwarded` → Coin Economy ledger insert
- Fraud protection: daily earn caps, duplicate source rejection
- Store catalog and purchase flow
- Inventory and equip cosmetics
- Flutter store screen and inventory management

**Exit Criteria:** User earns coins from gamification; can purchase and equip store items.

---

### Milestone 9 — Notifications
**Target:** Week 17

- FCM integration (Flutter + Laravel)
- Push notification triggers via domain event subscriptions (not direct module calls)
- In-app notification inbox
- Notification preferences in settings

**Exit Criteria:** Push notifications delivered on triggers; preferences respected; inbox functional.

---

### Milestone 10 — Referral System
**Target:** Week 18

- Referral code generation and deep linking
- Referral attribution via `UserRegistered` event; Reward Engine handles grants
- Anti-Cheat: referral fraud detection (same device, self-referral, circular)
- Flutter referral share screen

**Exit Criteria:** User can share referral link; referee registration credits both parties.

---

### Milestone 11 — Premium Subscription
**Target:** Week 19–20

- In-app purchase integration (Apple + Google)
- Subscription verification and status sync
- Feature gating via Feature Flags service
- Publish `PremiumPurchased` event on subscription
- Flutter paywall and subscription management

**Exit Criteria:** User can subscribe via app stores; premium features unlock; subscription status persists.

---

### Milestone 12 — Admin Panel
**Target:** Week 21–22

- Admin authentication and role middleware
- User management (view, suspend)
- Achievement/challenge/store catalog CRUD
- Rules Engine admin UI (XP rates, level thresholds, career titles, economy caps)
- Feature Flags admin UI
- Anti-cheat review queue
- Basic analytics dashboard

**Exit Criteria:** Admin can manage users, content, and view key metrics.

---

### Milestone 13 — Analytics & Observability
**Target:** Week 23

- Server-side analytics via domain event subscriptions
- Client-side analytics integration
- Crash reporting (Crashlytics)
- Admin analytics dashboard (DAU, retention, funnel)

**Exit Criteria:** Key events tracked; admin dashboard shows registration and trade metrics.

---

### Milestone 14 — Polish, Testing & Performance
**Target:** Week 24–25

- End-to-end integration testing of critical flows
- Performance optimization (API response times, Flutter rebuild optimization)
- Error handling audit across all screens
- Onboarding flow polish
- Accessibility review (contrast, font scaling)

**Exit Criteria:** Critical paths tested; API p95 < 500ms; no unhandled error states in primary flows.

---

### Milestone 15 — Version 1 Launch
**Target:** Week 26

- App Store and Google Play submission
- Production deployment with monitoring
- Legal pages (Terms of Service, Privacy Policy)
- Launch marketing assets
- Soft launch (limited geography or beta group)
- Monitor crash rate, API errors, and user feedback
- Full public launch

**Exit Criteria:** App live on both stores; production stable; monitoring active; support channel ready.

---

### Post-Launch (Weeks 27–30)

- Bug fix sprint based on user feedback
- Retention optimization (notification tuning, challenge balancing)
- Premium conversion optimization
- Performance monitoring and first scaling assessment

---

## 8. Risks

### 8.1 Technical Risks

| Risk | Impact | Likelihood | Mitigation |
|------|--------|-----------|------------|
| Stock data provider outage or rate limits | Users cannot see prices or trade | Medium | Multi-provider fallback; aggressive caching; graceful degradation UI |
| Floating-point errors in P&L calculation | Incorrect portfolio values, user trust loss | Medium | Store monetary values as integers (paise); use BCMath in PHP |
| Race conditions on concurrent trades | Double-spend of virtual cash | Medium | Database row locking on wallet; idempotency keys on trade requests |
| Hostinger shared hosting performance ceiling | Slow API at scale | High | Load test early; plan migration path to VPS at 50K users; optimize queries |
| Flutter state management complexity | Bugs in UI state sync | Medium | Strict Clean Architecture; one notifier per screen; integration tests |
| Queue worker reliability on shared hosting | Missed notifications, stale leaderboards | Medium | Cron-based queue worker with monitoring; dead letter queue logging |

### 8.2 Scaling Risks

| Risk | Impact | Likelihood | Mitigation |
|------|--------|-----------|------------|
| Leaderboard recalculation at 100K+ users | Timeout, stale rankings | High | Pre-computed snapshots via queue; materialized ranking tables; Redis cache layer |
| Portfolio valuation queries at scale | DB load spikes on market open | High | Snapshot portfolio values every 15 min; serve cached values; index holdings table |
| Stock quote cache stampede | Provider rate limit exceeded | Medium | Stagger cache expiry; single-flight pattern; bulk quote endpoint |
| MySQL single-instance bottleneck | Write contention on trades | Medium | Read replicas for reporting; connection pooling; migrate to managed DB |
| 1M user FCM notification blast | Delivery delays | Medium | Batch sending via queue; prioritize personalized over broadcast |

### 8.3 Legal Risks

| Risk | Impact | Likelihood | Mitigation |
|------|--------|-----------|------------|
| App perceived as real trading platform | Regulatory action (SEBI) | Medium | Clear disclaimers everywhere; "Paper Trading" in app name; no real money; legal review before launch |
| Stock data licensing violations | Cease and desist from data provider | Medium | Use licensed data provider; comply with redistribution terms; display required attributions |
| User data protection (India DPDP Act) | Fines, legal liability | Medium | Privacy policy compliant with DPDP; data minimization; account deletion flow; consent management |
| In-app purchase refund disputes | Revenue loss, store penalties | Low | Clear premium feature description; comply with Apple/Google refund policies |
| Gamification targeting minors | Regulatory scrutiny | Low | Age gate (13+ or 18+ — legal counsel to decide); no real-money gambling mechanics |
| Misleading portfolio performance claims | Consumer protection issues | Low | Always label as simulated; no promises of real market returns |

### 8.4 Security Risks

| Risk | Impact | Likelihood | Mitigation |
|------|--------|-----------|------------|
| API token theft | Account takeover | Medium | Short-lived tokens; refresh token rotation; device binding; anomalous login detection |
| Rate limiting bypass / bot abuse | XP farming, leaderboard manipulation | High | Rate limiting on all endpoints; CAPTCHA on registration; behavioral anti-cheat |
| SQL injection | Data breach | Low | Eloquent ORM parameterized queries; Form Request validation; regular security audit |
| Admin panel exposure | Full system compromise | Medium | IP whitelist for admin; separate admin auth; 2FA for admin (V1.1) |
| Sensitive data in logs | PII leakage | Medium | Log scrubbing; no password/token logging; structured logging review |
| Insecure direct object references | Access other users' data | Medium | Authorization checks on every resource endpoint; policy classes in Laravel |
| DDoS on API | Service unavailability | Medium | Cloudflare in front of API; rate limiting; Hostinger upgrade path |

### 8.5 Anti-Cheat & Abuse Risks

The Anti-Cheat System (Section 3.21) is the primary mitigation layer. Specific threat vectors:

| Threat | Detection Method | Mitigation |
|--------|-----------------|-----------|
| **Duplicate requests** | Idempotency key deduplication on trade and reward endpoints; reject if key seen within 24h | Require `Idempotency-Key` header on all mutation endpoints; store keys in Redis with TTL |
| **Rapid trading exploits** | Velocity monitor: max N trades per user per minute (Rules Engine configurable) | Temporary trade lock after threshold; flag account for review; no XP awarded for flagged trades |
| **Referral abuse** | Same device fingerprint, same IP cluster, self-referral (referrer = referee), circular referral chains | Block reward grant; require referee to complete ≥1 trade before referrer reward; monthly referral cap |
| **XP farming** | Action diversity scoring; diminishing returns on repeated action type; daily XP cap per source | Rules Engine configures caps; XP Engine enforces; excess XP silently discarded with audit log |
| **Leaderboard manipulation** | Abnormal portfolio value spikes (>X% in <Y minutes); synchronized trading across flagged account clusters | Exclude flagged accounts from leaderboard; admin review queue; automatic 7-day leaderboard ban on high-confidence flags |
| **Coin duplication** | Ledger idempotency on `source_type + source_id`; duplicate insert rejected | Append-only ledger; compensating transaction for corrections (never delete) |
| **Bot registration** | CAPTCHA on registration; device attestation (Play Integrity / App Attest) | Rate limit registration per IP; require email verification before wallet creation |

**Operational Response:**
- Flagged accounts enter admin review queue with full event audit trail.
- Admin can: dismiss flag, suspend account, reverse ledger entries (compensating transaction), or permanent ban.
- All anti-cheat actions are audit-logged with admin user ID.

---

## 9. Future Versions

### Version 2 — Social & Education (Months 2–4 post-launch)

- Friend system (follow, compare portfolios)
- Social feed (share trades, achievements)
- Beginner education module (lessons, quizzes)
- Hindi language support
- Improved onboarding with personalized learning path
- Friend leaderboards
- Weekly trading tournaments

### Version 3 — Advanced Trading & Analytics (Months 5–8)

- Limit orders and stop-loss orders
- Advanced portfolio analytics (Sharpe ratio, drawdown, sector exposure)
- Stock screener with custom filters
- Market news integration
- Watchlist sharing
- Options paper trading (simulated)
- Dark mode themes and premium cosmetic expansion

### Version 4 — Community & Platform (Months 9–12)

- User-published trading strategies (copy trading — paper only)
- AI-powered trade coach (personalized tips based on behavior)
- Community forums and discussion threads
- Broker referral partnerships (educational referral links, not execution)
- Web dashboard for portfolio management
- API for third-party integrations (educational institutions)
- Guild/team competitions
- Seasonal events and battle pass

*Detailed design for V2+ deferred to separate planning documents when V1 metrics justify investment.*

---

## 10. Self Review

### 10.1 Missing Modules Identified (Added During Review)

| Module | Reason Added |
|--------|-------------|
| **Onboarding** | First-run experience is distinct from auth; needs dedicated module for tutorial and cash grant ceremony |
| **Home Dashboard** | Central hub aggregating portfolio summary, challenges, and market pulse — implied but not explicitly listed |
| **Support / Help Center** | Production apps require FAQ, contact support, and bug report flow — add in Milestone 14 |
| **Legal / Compliance** | Terms of Service and Privacy Policy presentation — required for store submission |
| **Game Engine** | Added in v1.1 review — centralizes all game mechanics |
| **Rules Engine** | Added in v1.1 review — configurable game balance |
| **Feature Flags** | Added in v1.1 review — runtime feature control |
| **Anti-Cheat System** | Added in v1.1 review — cross-cutting abuse detection |
| **Market Data Layer** | Added in v1.1 review — isolates external provider access |

**Action:** Onboarding and Home Dashboard included in Flutter folder structure. Support and Legal added to Milestone 14 scope.

### 10.2 Weak Points Identified

1. **Hostinger shared hosting vs 1M user goal** — Fundamental tension. V1 launch is acceptable on shared hosting, but the architecture document must treat cloud migration as a planned Milestone 16 (post-launch), not an afterthought. **Revised:** Added explicit scaling migration trigger at 50K users in Section 8.

2. **Single stock data provider dependency** — No fallback provider specified. **Revised:** Mitigation in Section 8 includes multi-provider fallback strategy; ADR required before Milestone 3.

3. **Monetary value representation undecided** — Float vs integer (paise) affects entire trading engine. **Revised:** Section 6.6 flags this as ADR-required decision before Milestone 4.

4. **Admin panel as Blade views** — Acceptable for V1 but will not scale for complex admin needs. **Accepted for V1;** V2 should evaluate Filament PHP or separate admin SPA.

5. **Offline trading not addressed** — Users cannot trade offline. **Accepted:** Network required for trading actions is intentional (server-authoritative). Hive caches read-only data for browsing.

6. **League promotion logic complexity** — Weekly promotion/demotion with multiple leagues can cause user frustration. **Mitigation:** Document detailed league rules in `03_GAMIFICATION_DESIGN.md` before Milestone 5; include demotion protection for first 2 weeks; league rules managed via Rules Engine.

7. **Async event-driven gamification UX** — XP and coin updates arrive after trade response. **Mitigation:** Optimistic UI in Flutter; document UX pattern in Milestone 4; consider WebSocket push in V2.

### 10.3 Unnecessary Complexity Removed

| Removed | Reason |
|---------|--------|
| Microservices architecture | Premature for V1; monolithic Laravel is correct for startup phase |
| Event sourcing for trades | Over-engineered; standard CRUD with event dispatching suffices |
| GraphQL API | REST is simpler, better supported in Laravel, adequate for mobile client |
| Real-time WebSocket prices (V1) | Polling with 30–60s cache is sufficient for paper trading; WebSocket deferred to V3 |
| Separate admin API | Admin panel uses same Laravel app with web routes; no separate service needed |
| Kubernetes deployment | Absurd for V1 on Hostinger; removed from any V1 consideration |

### 10.4 Better Architectural Alternatives Considered

| Decision | Alternative Considered | Why Current Choice Wins (V1) |
|----------|----------------------|------------------------------|
| Laravel monolith | Node.js / Go microservices | Team velocity, Sanctum auth, Eloquent ORM, Hostinger PHP support |
| Riverpod | Bloc / Provider | Project rules mandate Riverpod; excellent for testability |
| MySQL | PostgreSQL | Hostinger default; MySQL sufficient at V1 scale |
| Hive local storage | SharedPreferences / SQLite | Project rules mandate Hive; good for structured cache |
| REST API | GraphQL | Simpler tooling, easier caching, adequate for mobile |
| FCM for push | OneSignal | FCM is free, native Flutter support, industry standard |
| Server-authoritative trading | Client-side simulation | Prevents cheating; leaderboard integrity requires server truth |

### 10.5 Final Architecture Confidence

| Area | Confidence | Notes |
|------|-----------|-------|
| Module boundaries | High | Event-driven decoupling with explicit pub/sub matrix |
| Game Engine separation | High | Clear ownership of all game mechanics; scalable extraction path |
| Tech stack alignment | High | Fully aligned with `project_rules.md` |
| V1 scope | High | Milestones are achievable in ~26 weeks with focused team |
| Scale path | High | Event contracts enable incremental service extraction at 100K/1M/10M |
| Legal compliance | Medium | Requires professional legal review before launch |
| Gamification balance | Medium | Rules Engine enables tuning without deploys; requires playtesting |
| Event ordering edge cases | Medium | Requires ADR on queue partitioning strategy before Milestone 4 |

---

## 11. Architecture Improvements Summary

The following enhancements were introduced during the Principal Architect review (v1.0 → v1.1). All preserve the original product vision.

| # | Enhancement | Section | Impact |
|---|------------|---------|--------|
| 1 | **Domain Event Architecture** | 3, 4 | Modules publish/subscribe via events; no direct cross-module calls for reactions |
| 2 | **Game Engine Module** | 3.6 | Centralized game mechanics (XP, Level, League, Reward, Mission, Season, Economy, Event Dispatcher) |
| 3 | **Career Progression Titles** | 3.6, 2 | 10 career titles (Student Trader → Market Legend) with feature, cosmetic, and gameplay unlocks |
| 4 | **Decoupled Trading Flow** | 3.4, 4 | Trade Request → Trading Engine → TradeExecuted → Portfolio → Game Engine → downstream engines |
| 5 | **Market Data Layer** | 3.18, 4 | Provider → Adapter → Cache → Market Data Service; Trading Engine never calls external APIs |
| 6 | **Ledger-Based Coin Economy** | 3.10 | Append-only coin transactions with Wallet, Reward Sources, Audit Trail, Fraud Protection |
| 7 | **Feature Flags** | 3.20 | Runtime feature gating (crypto, options, battle pass, AI coach, copy trading, tournaments) |
| 8 | **Rules Engine** | 3.19 | Configurable XP, coin, challenge, league, level, season, and economy rules — no code deploys |
| 9 | **Anti-Cheat System** | 3.21, 8.5 | Cross-cutting abuse detection for trades, referrals, XP, leaderboards, and coin duplication |
| 10 | **Future Scalability Path** | 4 | Incremental evolution plan for 100K, 1M, and 10M users with service extraction map |
| 11 | **Event Subscriber Matrix** | 4 | Explicit publish/subscribe mapping for all 10 core domain events |
| 12 | **Updated Dependency Graph** | 3 | Event-driven graph replacing direct module coupling |
| 13 | **Updated Folder Structure** | 5 | Game Engine, Market Data, Economy, Rules, Feature Flags, Anti-Cheat service directories |
| 14 | **Updated Roadmap** | 7 | Milestones aligned to event-driven and Game Engine architecture |
| 15 | **New Documentation Plan** | 5.3 | Added `05_DOMAIN_EVENTS.md` and `08_RULES_ENGINE.md` to docs scaffold |

---

## 12. Pre-Coding Architecture Review

A final critical review before implementation begins. Items marked **Block** must be resolved in documentation (ADR or spec) before the relevant milestone starts.

### Resolved by v1.1 Improvements

| Item | Status |
|------|--------|
| Direct module coupling in trading flow | ✅ Resolved — event-driven architecture |
| Gamification logic scattered across modules | ✅ Resolved — Game Engine centralization |
| No career identity layer | ✅ Resolved — Career Progression titles |
| Stock data accessed directly by Trading Engine | ✅ Resolved — Market Data Layer |
| Coin balance mutation risk | ✅ Resolved — ledger-based economy |
| No runtime feature control | ✅ Resolved — Feature Flags module |
| Hardcoded game balance values | ✅ Resolved — Rules Engine |
| Insufficient anti-cheat design | ✅ Resolved — Anti-Cheat System + Section 8.5 |
| No scale evolution plan | ✅ Resolved — Future Scalability Path |

### Still Requires Resolution Before Coding

| Item | Priority | Required Before | Action |
|------|----------|----------------|--------|
| **Monetary value representation** (paise integer vs decimal string) | Block | Milestone 4 | Create ADR `004-coin-ledger-model.md` — affects trading, portfolio, and coin ledger |
| **Domain event queue partitioning strategy** | Block | Milestone 4 | Document in `05_DOMAIN_EVENTS.md` — user_id-based queue keys vs global queues |
| **Market data provider selection** | Block | Milestone 3 | Create ADR `002-market-data-provider.md` with cost, coverage, and failover analysis |
| **Career title level thresholds final values** | High | Milestone 5 | Document in `03_GAMIFICATION_DESIGN.md` — initial values in Rules Engine, tunable post-launch |
| **Coin ledger balance caching strategy** | High | Milestone 8 | Decide: computed on read vs materialized balance refreshed on write (recommend materialized with ledger as source of truth) |
| **Flutter async gamification UX** | High | Milestone 4 | Define UX pattern for async XP/coin updates after trade (optimistic UI vs poll vs WebSocket push in V2) |
| **Event idempotency key schema** | High | Milestone 4 | Standardize `{event_type}:{source_id}` format across all listeners |
| **Rules Engine JSON schema** | Medium | Milestone 5 | Define rule document schema in `08_RULES_ENGINE.md` before admin UI built |
| **Feature flag cache invalidation** | Medium | Milestone 1 | Define TTL and force-refresh mechanism on app foreground |
| **Legal review for gamification mechanics** | Medium | Milestone 15 | Professional review of coin economy, streaks, and leaderboard prizes |
| **Dead letter queue monitoring** | Medium | Milestone 4 | Define alerting threshold and admin recovery procedure for failed event listeners |
| **Portfolio cash validation on trade** | Medium | Milestone 4 | ✅ Addressed — Trading Engine performs read-only wallet/holdings check before accepting order; Portfolio listener applies mutation async |

### Architectural Risks Still Present (Accepted for V1)

| Risk | Acceptance Rationale |
|------|---------------------|
| Laravel Events may not scale to 1M users without migration to Kafka | Accepted — migration path documented; event contracts are stable |
| Async gamification creates brief UI inconsistency after trades | Accepted — optimistic UI pattern recommended; resolves in < 2s typically |
| Rules Engine adds DB read on every XP/trade calculation | Accepted — mitigated by config cache; Redis cache at 100K users |
| Hostinger queue worker reliability | Accepted — migrate to VPS at 50K users; dead letter queue as safety net |
| Battle Pass and Season Engine built but disabled in V1 | Accepted — infrastructure ready via Feature Flags; reduces V2 rework |

### Recommended Documentation Order Before Milestone 1

1. `adr/003-domain-event-bus.md` — event delivery, idempotency, queue partitioning
2. `adr/002-market-data-provider.md` — provider selection
3. `adr/004-coin-ledger-model.md` — monetary representation and ledger schema
4. `05_DOMAIN_EVENTS.md` — full event catalog with payload schemas
5. `03_GAMIFICATION_DESIGN.md` — career titles, XP weights, league rules
6. `08_RULES_ENGINE.md` — rule document JSON schema

---

## Appendix A — Glossary

| Term | Definition |
|------|-----------|
| Paper Trading | Simulated trading with virtual money, no real financial risk |
| Virtual Cash | In-app currency (INR) used exclusively for paper trades |
| XP | Experience points earned through app activities |
| Career Title | Narrative rank (Student Trader → Market Legend) mapped to level ranges |
| Game Engine | Central module orchestrating all game mechanics via sub-engines |
| Domain Event | Immutable record of a business occurrence; published by one module, consumed by subscribers |
| League | Competitive tier (Bronze through Diamond) based on weekly performance |
| Coin | Soft currency earned through gameplay, spent in the store; ledger-based, never directly mutated |
| Coin Ledger | Append-only transaction log; source of truth for coin balances |
| Rules Engine | Database-driven configuration for all game balance values |
| Feature Flag | Runtime toggle enabling/disabling features without app store release |
| Market Data Service | Internal API layer; sole entry point for stock price data |
| Anti-Cheat System | Cross-cutting abuse detection module subscribing to domain events |
| P&L | Profit and Loss — realized (closed trades) and unrealized (open holdings) |
| NSE/BSE | National Stock Exchange / Bombay Stock Exchange of India |
| Sanctum | Laravel's lightweight API authentication system |
| ADR | Architecture Decision Record — documents significant technical choices |

---

## Appendix B — Document Maintenance

| Trigger | Action |
|---------|--------|
| New feature module added | Update Section 3 and dependency graph |
| Domain event added or changed | Update Section 4 event matrix; update `05_DOMAIN_EVENTS.md` |
| Architecture review completed | Update Section 11 (Improvements Summary) and Section 12 (Pre-Coding Review) |
| Tech stack change | Update Sections 4, 5, 6; create ADR |
| Milestone completed | Mark complete in Section 7 |
| Risk materialized | Update Section 8 with post-mortem |
| V2 planning begins | Expand Section 9 with detailed specs in new doc |

**Owner:** Chief Software Architect / Technical Lead  
**Review Cadence:** Monthly during active development; quarterly post-launch

---

*End of Master Architecture Document*
