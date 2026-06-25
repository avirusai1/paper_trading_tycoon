# Paper Trading Tycoon — Project Foundation Report

**Generated:** June 2025  
**Scope:** Production skeleton — all core systems, zero feature implementations  
**Status:** Foundation complete. Ready for Milestone 1 feature development.

---

## Consistency Verification

All automated checks passed on final review:

| Check | Result |
|-------|--------|
| Clean Architecture layers (16 features × 3 layers) | ✅ 16/16 complete |
| PHP `declare(strict_types=1)` | ✅ 93/93 files |
| Laravel listeners implement `ShouldQueue` | ✅ 14/14 listeners |
| Float monetary arithmetic in PHP | ✅ None found |
| Flutter domain entities extend `Equatable` or use `@freezed` | ✅ 16/16 entities |

---

## Files Created

### Flutter (`frontend/`)

**Configuration**
- `pubspec.yaml` — full dependency manifest (riverpod, go_router, dio, hive, firebase, etc.)
- `analysis_options.yaml` — strict lints + riverpod_lint
- `.gitignore` — excludes .env, Firebase config files
- `.editorconfig`

**Entry & Root**
- `lib/main.dart` — Firebase init, Hive init, Crashlytics, ProviderScope
- `lib/app.dart` — root widget consuming appRouterProvider + themeModeProvider

**Core Layer** (`lib/core/`)
- `constants/app_constants.dart` — starting cash (100000000 paise), debounce, pagination, retry
- `constants/api_constants.dart` — all `/api/v1/` endpoint paths
- `constants/storage_keys.dart` — HiveBoxNames, StorageKeys, SecureStorageKeys
- `env/app_env.dart` — envied-based compile-time env injection
- `errors/exceptions.dart` — sealed AppException hierarchy (10 subclasses)
- `errors/failures.dart` — sealed Failure hierarchy with Equatable
- `errors/error_mapper.dart` — AppException → Failure switch expression
- `theme/app_colors.dart` — brand + semantic + league tier colours
- `theme/app_spacing.dart` — AppSpacing, AppRadius, AppElevation, AppDurations
- `theme/app_theme.dart` — lightTheme, darkTheme, themeModeProvider
- `theme/app_typography.dart` — Material 3 text theme
- `utils/formatters.dart` — paise(), paisePnl(), signedPercent(), relativeTime(), stockPrice()
- `utils/validators.dart` — email, password, phone validators
- `utils/debouncer.dart` — Timer-based debounce utility
- `utils/logger.dart` — structured logger wrapper (disabled in release)
- `extensions/context_extensions.dart`
- `extensions/datetime_extensions.dart`
- `extensions/num_extensions.dart`

**Network Layer** (`lib/services/api/`)
- `dio_client.dart` — Dio instance factory with interceptor stack
- `api_client.dart` — Laravel envelope unwrapper, idempotency key support
- `auth_interceptor.dart` — token injection + silent refresh with mutex + pending queue
- `retry_interceptor.dart` — exponential backoff, safe-method detection, idempotency-key awareness
- `error_interceptor.dart` — DioException → AppException mapping, 422 field error extraction
- `logging_interceptor.dart` — structured request/response logging

**Storage Layer** (`lib/services/storage/`)
- `hive_service.dart` — Hive initialization, box registration
- `secure_storage_service.dart` — token CRUD, hasValidToken() with 30s buffer
- `preference_manager.dart` — onboarding state, theme pref
- `cache_manager.dart` — generic cache with TTL

**Other Services**
- `services/feature_flags/feature_flag_service.dart` — isEnabled(), % rollout, Hive cache
- `services/connectivity/connectivity_service.dart`
- `services/notification/push_service.dart` — FCM token registration
- `services/analytics/analytics_service.dart`

**Navigation** (`lib/routes/`)
- `app_router.dart` — GoRouter with ShellRoute (5 tabs), all routes compile via _PlaceholderScreen
- `route_guards.dart` — onboarding → auth → email verification redirect chain
- `route_names.dart` — typed route name constants

**Shared Widgets** (`lib/shared/widgets/`)
- `buttons/primary_button.dart` — loading spinner state
- `buttons/secondary_button.dart` — outlined variant
- `cards/app_card.dart` — Material 3 card with GradientCard variant
- `dialogs/confirm_dialog.dart` — isDestructive support
- `inputs/app_text_field.dart`
- `loaders/app_loading_indicator.dart`
- `snackbars/app_snackbar.dart` — success/error/info helpers
- `states/error_state_widget.dart` — icon adapts by Failure subtype
- `states/empty_state_widget.dart`

**Feature Scaffolds** (16 features × 6 files = 96 files)  
Features: `auth`, `home`, `stock_market`, `trading`, `portfolio`, `gamification`, `achievements`, `challenges`, `leaderboards`, `store`, `premium`, `notifications`, `referral`, `settings`, `profile`, `onboarding`  
Per feature: domain entity, repository interface, remote data source interface, local data source interface, repository implementation (stub), presentation provider (stub)

**Tests**
- `test/unit/core/formatters_test.dart`
- `test/unit/core/validators_test.dart`
- `test/helpers/test_helpers.dart`

**Total Flutter files:** ~142

---

### Laravel (`backend/`)

**Configuration**
- `composer.json` — PHP 8.3, Laravel 12, Sanctum 4, larastan, pint, phpunit
- `.env.example` — all required keys documented
- `phpstan.neon` — level 8 strict analysis
- `pint.json` — code style configuration
- `.gitignore`, `.editorconfig`
- `config/gamification.php` — career titles, XP weights, coin amounts, league thresholds
- `config/feature_flags.php` — all flags default false
- `config/market_data.php` — provider config, cache TTL
- `config/trading.php` — position limits, order config
- `routes/api.php` — all v1 route groups

**Domain Events** (`app/Events/`) — 10 events
`UserRegistered`, `TradeExecuted`, `PortfolioUpdated`, `XPGranted`, `LevelUp`, `ChallengeCompleted`, `AchievementUnlocked`, `CoinsAwarded`, `SeasonRewardGranted`, `PremiumPurchased`

**Event Listeners** (`app/Listeners/`) — 14 listeners across 5 namespaces  
Achievement, Analytics (×2), AntiCheat (×2), Game (×3), Notification (×3), Portfolio (×2), Trading (×1)  
All implement `ShouldQueue`.

**Enums** (`app/Enums/`)
`LeagueTier`, `CoinTransactionSource`, `AchievementTier`, `CareerTitle`, `MarketStatus`, `OrderSide`, `PremiumPlan`

**DTOs** (`app/DTOs/`)
`StockQuoteDTO`, `PlaceOrderDTO`, `RegisterUserDTO`, `XPGrantDTO`

**Services** (`app/Services/`)
- `Game/XPEngine.php`, `LevelEngine.php`, `LeagueEngine.php`, `RewardEngine.php`, `MissionEngine.php`, `SeasonEngine.php`, `EventDispatcher.php`
- `Rules/RulesEngine.php` — DB-driven game balance values
- `Features/FeatureFlagService.php` — full implementation with % rollout
- `Economy/CoinLedgerService.php`
- `MarketData/MarketDataService.php`, `ProviderAdapter.php`, `QuoteCache.php`
- `Trading/TradingEngine.php`
- `Portfolio/PortfolioService.php`
- `Notification/NotificationService.php`
- `Security/AntiCheatService.php`
- `BaseService.php`

**Actions** (`app/Actions/`)
`ExecuteBuyOrderAction`, `ExecuteSellOrderAction`, `GrantXPAction`, `AwardCoinsAction`, `RegisterUserAction`

**HTTP Layer**
- `Http/Controllers/Api/V1/`: `BaseApiController`, `AuthController`, `HealthController`, `FeatureFlagController`
- `Http/Middleware/`: `IdempotencyMiddleware`, `RequestIdMiddleware`, `EnsureEmailVerified`
- `Http/Responses/ApiResponse.php` — success/paginated/error envelope
- `Http/Resources/Api/V1/` — 18 JSON resources

**Foundation**
- `Exceptions/Handler.php` — global exception → JSON response
- `Exceptions/DomainException.php` — abstract with errorCode() + httpStatus()
- `Helpers/MoneyHelper.php` — bcmath paise arithmetic
- `Helpers/LogHelper.php`
- `Repositories/Contracts/BaseRepositoryContract.php`
- `Repositories/Eloquent/BaseEloquentRepository.php`
- `Providers/AppServiceProvider.php` — singleton + transient DI bindings
- `Providers/EventServiceProvider.php` — complete $listen map

**Tests**
- `tests/Unit/Helpers/MoneyHelperTest.php` — full coverage inc. edge cases
- `tests/Feature/HealthCheckTest.php`
- `tests/Feature/FeatureFlagsTest.php`
- `tests/TestCase.php`

**Total Laravel files:** ~93

---

### CI/CD
- `.github/workflows/flutter-ci.yml`
- `.github/workflows/laravel-ci.yml`

### Documentation (`docs/`)
- `README.md` (root)
- `docs/CODING_GUIDELINES.md`
- `docs/FOLDER_STRUCTURE.md`
- `docs/DEPENDENCY_GUIDE.md`
- `docs/adr/001-tech-stack.md`
- `docs/adr/002-market-data-provider.md`
- `docs/adr/003-domain-event-bus.md`
- `docs/adr/004-coin-ledger-model.md`

**Grand total: ~250 files**

---

## Files Modified

No pre-existing files were modified. All source docs (`docs/`, `cursor_rules/`) were read-only inputs and remain unchanged.

---

## Architecture Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Flutter state management | Riverpod 2.5 | Mandated; compile-time safety, testability |
| Navigation | GoRouter with ShellRoute | Deep links, auth guard via redirect callback |
| HTTP client | Dio + 4 interceptors | First-class interceptor stack; no competitor matches |
| Token refresh | Mutex + pending queue | Prevents concurrent refresh race conditions |
| Retry strategy | Exponential backoff, safe methods only | Prevents duplicate trade orders on retry |
| Local storage | Hive (general) + Flutter Secure Storage (tokens) | Tokens need OS keychain; Hive is unencrypted |
| Feature flags | DB-backed + Hive cache + % rollout | Runtime gating without app release; A/B ready |
| Monetary representation | Paise integers + bcmath | Eliminates IEEE 754 float errors in portfolio P&L |
| Coin balance | Append-only ledger | Full audit trail; economy rebalancing via compensating inserts |
| Inter-module communication | Domain events only (never direct service calls) | Decoupling; async processing; swap-safe |
| Listener execution | All ShouldQueue | Non-blocking trade response; resilient to listener failures |
| Game balance values | Rules Engine (DB, not code) | Balance tuning without code deployments |
| PHP static analysis | PHPStan level 8 | Catches type/null errors before runtime |
| Error handling (Flutter) | `Either<Failure, T>` + sealed classes | No exception propagation through domain; exhaustive UI handling |
| Market data security | Backend adapter only; Flutter never calls provider | API keys never in mobile binary |

---

## Potential Improvements

**Flutter**
- Add `riverpod_generator` code generation for all providers now that the scaffold is in place — reduces boilerplate significantly.
- `shimmer` placeholders are defined but not wired to loading states; feature teams should adopt them consistently.
- Route guards currently check `hasValidToken()` synchronously — consider a `FutureProvider<AuthState>` to unify auth state across the guard and UI.

**Laravel**
- `RulesEngine` is a singleton that reads from DB on first call — add a cache layer with tag-based invalidation to avoid N+1 on heavy game event processing.
- `FeatureFlagService` handles guest users (null userId) with all-false defaults — consider a separate `GuestFeatureFlagService` to keep the responsibility clean.
- `MarketDataService` + `ProviderAdapter` are interfaces pending concrete implementation — `MockMarketDataAdapter` should ship in the skeleton to unblock feature development without a real API key.

---

## Technical Debt

| Item | Location | Impact | Owner |
|------|----------|--------|-------|
| Market data provider not decided | `ADR-002` | Blocks Milestone 3 entirely | Tech Lead |
| MockMarketDataAdapter not implemented | `backend/app/Services/MarketData/` | Blocks local dev without API key | Backend Dev |
| `_PlaceholderScreen` in all routes | `frontend/lib/routes/app_router.dart` | Expected; replaced as screens are built | Feature teams |
| Database migrations not created | `backend/database/migrations/` | Blocks any feature that needs persistence | Backend Dev |
| No Eloquent model files | `backend/app/Models/` | Repositories are stubs; models needed for queries | Backend Dev |
| Firebase config files not committed | `frontend/android/app/`, `frontend/ios/Runner/` | Expected (gitignored); each dev must download from console | All devs |
| `build_runner` generated files not committed | `frontend/lib/**/*.g.dart`, `*.freezed.dart` | Expected; must run `dart run build_runner build` after clone | All Flutter devs |

---

## Future Recommendations

**Short term (before Milestone 2)**
- Implement `MockMarketDataAdapter` — all game feature development depends on being able to simulate price changes locally.
- Create database migrations for the 5 core tables: `users`, `portfolios`, `holdings`, `trades`, `coin_transactions`. These unblock every other milestone.
- Create Eloquent models with typed properties and relationships.

**Medium term (before 10K users)**
- Migrate queue driver from `database` to Redis (per ADR-003 partitioning strategy).
- Introduce a `PortfolioCalculationJob` that pre-computes portfolio values on a schedule, avoiding real-time aggregation on every leaderboard request.
- Add integration test coverage for the trading flow end-to-end (PlaceOrder → TradeExecuted event → PortfolioUpdated → XPGranted chain).

**Long term (scale)**
- When the Rules Engine DB table grows beyond ~200 rows, add a Redis-backed cache layer with admin UI for live balance tuning.
- Evaluate extracting `GameEngine` to a separate Laravel package once the domain stabilises — makes it testable in isolation and potentially reusable.
- At 100K users, re-evaluate shared hosting per ADR-001 risk register trigger. VPS migration plan should be drafted at 30K DAU, not 50K.
- Add PHPStan baseline (`phpstan-baseline.neon`) early rather than letting it accumulate — zero-baseline policy is much easier to enforce from the start than to retrofit.
