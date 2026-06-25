# Paper Trading Tycoon — Dependency Guide

Explains why each dependency was chosen and what it must not be swapped for without an ADR.

---

## Flutter Dependencies

### State Management
**`flutter_riverpod: 2.5.1`**  
Mandated by `project_rules.md`. Provider-based DI + state management with compile-time safety. Code generation via `riverpod_generator` (part of `riverpod_annotation`) produces boilerplate-free notifiers. `riverpod_lint` enforces patterns at analysis time.

**Do not swap to:** Bloc, GetX, or Provider.

### Navigation
**`go_router: 13.2.0`**  
Declarative URL-based routing with shell routes for bottom nav tabs and `redirect` callbacks for auth guards. Deep link support built-in. Integrates with Riverpod via `ref.watch` in `redirect`.

### HTTP Client
**`dio: 5.4.3+1`**  
Interceptor stack (Auth → Retry → Error → Logging) is the primary reason. `http` package has no first-class interceptor support. `retrofit` generators are layered on top.

### Local Storage
**`hive_flutter: 1.1.0`**  
Binary key-value store backed by typed adapters. Fast reads for feature flags and stock cache. `flutter_secure_storage: 9.0.0` handles auth tokens via OS keychain — **tokens must never go in Hive** (unencrypted on disk).

### Serialization
**`freezed_annotation` + `json_annotation`**  
`freezed` generates immutable value types with `copyWith`, pattern matching, and equality. `json_serializable` generates `fromJson`/`toJson`. Both require `build_runner`.

### Functional Error Handling
**`dartz: 0.10.1`**  
Provides `Either<Failure, T>` for repository return types. Avoids try/catch propagation through domain layers.

### Firebase
**`firebase_core`, `firebase_messaging`, `firebase_crashlytics`**  
Crashlytics captures unhandled Flutter errors in production. FCM handles push notifications. `firebase_core` is the shared initialization layer.

### Utilities
| Package | Version | Purpose |
|---------|---------|---------|
| `equatable` | 2.0.5 | Value equality on domain entities without `==` boilerplate |
| `intl` | 0.19.0 | `NumberFormat` and `DateFormat` for INR display strings |
| `uuid` | 4.4.0 | V4 UUIDs for idempotency keys on trade requests |
| `connectivity_plus` | 6.0.3 | Online/offline detection for retry UX |
| `logger` | 2.3.0 | Structured log output with level filtering; disabled in release |
| `envied` | 0.5.4+1 | Compile-time env var injection — secrets baked into binary, not bundled as plaintext `.env` |
| `cached_network_image` | latest | Company logo / stock icon caching |
| `lottie` | 3.1.2 | JSON animation files for achievement unlocks, level-up celebrations |
| `shimmer` | 3.0.0 | Skeleton loading screens while data fetches |

---

## Laravel / PHP Dependencies

### Framework
**`laravel/framework: 12.x`**  
LTS baseline. Event system, queue workers, Eloquent ORM, and Sanctum are all first-party. Keeps the dependency tree small.

### Authentication
**`laravel/sanctum: 4.0`**  
Mobile token authentication without the OAuth overhead of Passport. Tokens stored hashed in DB. Token expiry via `SANCTUM_TOKEN_EXPIRY_MINUTES` config.

### Static Analysis
**`larastan/larastan: 3.0`** (wraps PHPStan)  
Level 8 strictness enforced in CI. Catches null dereferences, wrong types, and missing return types before runtime. The `phpstan.neon` baseline file may be committed for known legacy ignores — kept at zero entries for new code.

### Code Style
**`laravel/pint: 1.13`**  
Opinionated PSR-12 formatter. `vendor/bin/pint --test` in CI (no changes allowed). `vendor/bin/pint` locally auto-fixes. Configuration in `pint.json`.

### Testing
**`phpunit/phpunit: 11.0`**  
Laravel's built-in test runner. Feature tests use `RefreshDatabase`. Unit tests mock repositories. Parallel test execution via `php artisan test --parallel` reduces CI time.

---

## Version Lock Policy

Dependencies at exact minor versions (e.g. `2.5.1` not `^2.5.0`) for Flutter packages that have caused breaking changes between minor versions (go_router, riverpod). Laravel PHP packages use `^` range per Composer convention.

Dependency upgrades require: local test pass → CI pass → PR with `chore(deps):` commit message.
