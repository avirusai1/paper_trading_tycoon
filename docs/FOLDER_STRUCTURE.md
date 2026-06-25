# Paper Trading Tycoon — Folder Structure

This document describes the canonical folder layout. Deviating from this structure requires an ADR.

---

## Repository Root

```
paper_trading_tycoon/
├── frontend/               # Flutter application
├── backend/                # Laravel 12 API
├── docs/                   # Architecture documentation
│   └── adr/                # Architecture Decision Records
├── .github/
│   └── workflows/          # CI/CD pipelines
└── README.md
```

---

## Flutter: `frontend/lib/`

```
lib/
├── main.dart               # Entry point — Firebase init, Hive init, ProviderScope
├── app.dart                # Root widget — consumes appRouterProvider, themeModeProvider
│
├── core/
│   ├── constants/
│   │   ├── app_constants.dart      # Timing, pagination, virtual cash amounts
│   │   ├── api_constants.dart      # All /api/v1/ endpoint paths
│   │   └── storage_keys.dart       # Hive box names, storage keys, secure keys
│   ├── errors/
│   │   ├── exceptions.dart         # Sealed AppException hierarchy
│   │   ├── failures.dart           # Sealed Failure hierarchy (Equatable)
│   │   └── error_mapper.dart       # AppException → Failure conversion
│   ├── theme/
│   │   ├── app_colors.dart         # Brand colours, semantic colours, league tier colours
│   │   ├── app_spacing.dart        # AppSpacing, AppRadius, AppElevation, AppDurations
│   │   └── app_theme.dart          # lightTheme, darkTheme, themeModeProvider
│   └── utils/
│       └── formatters.dart         # paise(), paisePnl(), signedPercent(), relativeTime()
│
├── routes/
│   ├── app_router.dart             # GoRouter definition, ShellRoute for main tabs
│   └── route_guards.dart           # Auth/onboarding redirect logic
│
├── services/
│   ├── api/
│   │   ├── api_client.dart         # Dio wrapper, envelope unwrapping, idempotency
│   │   ├── auth_interceptor.dart   # Token injection + silent refresh with mutex
│   │   ├── retry_interceptor.dart  # Exponential backoff, safe-method detection
│   │   ├── logging_interceptor.dart
│   │   └── error_interceptor.dart  # DioException → AppException mapping
│   ├── storage/
│   │   ├── hive_service.dart       # Hive initialization, box registration
│   │   └── secure_storage_service.dart  # Token CRUD, hasValidToken()
│   └── feature_flags/
│       └── feature_flag_service.dart  # isEnabled(), percentage rollout, Hive cache
│
├── shared/
│   └── widgets/
│       ├── buttons/                # PrimaryButton, SecondaryButton
│       ├── feedback/               # AppLoadingIndicator, ErrorStateWidget, EmptyStateWidget
│       ├── dialogs/                # ConfirmDialog, snackbar helpers
│       ├── forms/                  # AppTextField
│       ├── cards/                  # AppCard, GradientCard
│       └── shimmer/                # ShimmerListTile, ShimmerCard
│
└── features/
    ├── auth/                       # Login, register, onboarding
    ├── home/                       # Dashboard / home screen
    ├── market/                     # Stock listing, search, quotes
    ├── stock_detail/               # Single stock detail + chart
    ├── trading/                    # Buy/sell order flow
    ├── portfolio/                  # Holdings, P&L, performance
    ├── game_hud/                   # XP bar, level badge, coins overlay
    ├── achievements/               # Achievement list + unlock animations
    ├── challenges/                 # Daily/weekly challenges
    ├── leaderboard/                # League standings
    ├── store/                      # Coin store
    ├── premium/                    # Premium feature upsell
    ├── notifications/              # Notification inbox
    ├── referral/                   # Referral code + tracking
    ├── settings/                   # App settings, preferences
    └── profile/                    # User profile screen
```

Each feature follows Clean Architecture layers:

```
features/[feature]/
├── domain/
│   ├── entities/           # Immutable domain models (Equatable or Freezed)
│   └── repositories/       # Abstract repository interfaces
├── data/
│   ├── models/             # API response models with .toDomain()
│   ├── data_sources/       # Abstract remote/local data source interfaces
│   └── repositories/       # Concrete repository implementations
└── presentation/
    ├── providers/          # Riverpod providers and notifiers
    ├── screens/            # Screen widgets (consume providers)
    └── widgets/            # Feature-local widgets
```

---

## Laravel: `backend/app/`

```
app/
├── Http/
│   ├── Controllers/Api/V1/     # One controller per domain area
│   ├── Middleware/             # IdempotencyMiddleware, RateLimitMiddleware
│   ├── Requests/               # FormRequest validation classes
│   └── Responses/
│       └── ApiResponse.php     # Standard JSON envelope
│
├── Models/                     # Eloquent models
│
├── DTOs/                       # Data transfer objects
│   ├── Market/
│   ├── Trading/
│   └── Game/
│
├── Enums/                      # PHP 8.1+ backed enums
│   ├── LeagueTier.php
│   ├── CoinTransactionSource.php
│   └── (others)
│
├── Events/                     # Domain event classes (10 core events)
│
├── Listeners/                  # Event listeners (all implement ShouldQueue)
│
├── Services/
│   ├── Auth/
│   ├── Trading/
│   ├── Market/
│   ├── Game/
│   │   ├── Engines/            # XP, Level, League, Reward, Mission, Season, Economy engines
│   │   └── RulesEngine.php     # DB-driven game balance values
│   ├── Features/
│   │   └── FeatureFlagService.php
│   └── AntiCheat/
│
├── Repositories/               # Eloquent repository implementations
│
├── Helpers/
│   └── MoneyHelper.php         # Paise arithmetic via bcmath
│
├── Exceptions/
│   ├── Handler.php             # Global exception → JSON response mapping
│   └── DomainException.php     # Abstract base with errorCode() + httpStatus()
│
└── Providers/
    ├── AppServiceProvider.php  # DI bindings (singletons + transients)
    └── EventServiceProvider.php    # $listen array — all 10 events wired
```

---

## Documentation: `docs/`

```
docs/
├── adr/                        # Architecture Decision Records
│   ├── 001-tech-stack.md
│   ├── 002-market-data-provider.md
│   ├── 003-domain-event-bus.md
│   └── 004-coin-ledger-model.md
│
├── CODING_GUIDELINES.md        # Naming, patterns, forbidden practices
├── FOLDER_STRUCTURE.md         # This file
├── DEPENDENCY_GUIDE.md         # Why each dependency was chosen
├── API_SPEC.md                 # OpenAPI-style endpoint reference (stub)
└── DATABASE_PLAN.md            # Schema overview, migration strategy (stub)
```
