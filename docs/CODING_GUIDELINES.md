# Paper Trading Tycoon — Coding Guidelines

**Version:** 1.0  
**Applies to:** All code in `frontend/` and `backend/`

---

## Core Principles

1. **Production code only** — no demo code, no placeholder implementations, no mock data in non-test files.
2. **Clean Architecture** — presentation → domain → data. Never skip layers.
3. **One public class per file** (both Dart and PHP).
4. **Explicit over implicit** — type everything, name everything clearly.
5. **Fail loudly in development, fail gracefully in production.**

---

## Flutter / Dart Standards

### Naming
| Context | Convention | Example |
|---------|-----------|---------|
| Classes | PascalCase | `PortfolioRepository` |
| Variables, methods | camelCase | `totalPortfolioValue` |
| Files | snake_case | `portfolio_repository.dart` |
| Constants | camelCase | `startingCash` |
| Private members | `_camelCase` | `_dioClient` |

### Architecture Rules
- Use cases depend on repository **interfaces** (domain layer), never implementations (data layer).
- Providers own Riverpod state. Screens are stateless consumers.
- Data layer models are separate from domain entities — `UserModel` ≠ `AuthUser`.
- All `Either<Failure, T>` pattern for repository return types.

### Error Handling
```dart
// ✅ Correct
try {
  final result = await remoteDataSource.fetchPortfolio();
  return Right(result.toDomain());
} on AppException catch (e) {
  return Left(ErrorMapper.fromException(e));
}

// ❌ Wrong — swallowing exceptions
try { ... } catch (e) { return Left(UnexpectedFailure()); }
```

### State Management
- One notifier per screen. Never share notifiers between unrelated screens.
- Use `AsyncNotifier` for async state, `Notifier` for sync state.
- Keep providers near their consumers — avoid global provider files.

---

## Laravel / PHP Standards

### Naming
| Context | Convention | Example |
|---------|-----------|---------|
| Classes | PascalCase | `TradingEngine` |
| Methods | camelCase | `executeBuyOrder` |
| Variables | camelCase | `$portfolioValue` |
| DB tables | snake_case, plural | `user_achievements` |
| DB columns | snake_case | `average_buy_price` |
| API endpoints | kebab-case, plural nouns | `/api/v1/stock-quotes` |
| API JSON fields | snake_case | `{ "total_value": 1500000 }` |

### Architecture Rules
- Controllers → validate → delegate to Service or Action. Never query DB in controllers.
- Services → orchestrate. May call repositories, action classes, and dispatch events.
- Repositories → data access only. No business logic.
- Actions → single operation. Receives a DTO. Performs one business operation.

### Strict Types
Every PHP file must begin with:
```php
<?php
declare(strict_types=1);
```

### Monetary Values
```php
// ✅ Always paise integers
$totalValuePaise = MoneyHelper::multiply($pricePaise, $quantity);

// ❌ Never float arithmetic for money
$totalValue = $price * $quantity; // FORBIDDEN
```

### API Responses
```php
// ✅ Always use ApiResponse
return ApiResponse::success($resource, 'Trade executed successfully.', 201);

// ❌ Never return raw arrays
return response()->json(['data' => $trade]); // FORBIDDEN
```

---

## Git Workflow

- **main** — production-ready code only. Protected branch.
- **develop** — integration branch. All features merge here.
- **feature/PTT-{ticket}-short-description** — feature branches.
- **fix/PTT-{ticket}-short-description** — bug fix branches.

### Commit Format
```
type(scope): short description (#ticket)

Longer description if needed.
```

Types: `feat`, `fix`, `refactor`, `test`, `docs`, `chore`.

Example: `feat(trading): add buy order idempotency validation (#PTT-45)`

---

## Code Review Checklist

Before submitting a PR:
- [ ] `flutter analyze` passes with zero warnings
- [ ] PHPStan level 8 passes
- [ ] `vendor/bin/pint` reports no changes
- [ ] All new code has unit tests
- [ ] No hardcoded monetary values — use `MoneyHelper`
- [ ] No direct Eloquent queries in controllers or service constructors
- [ ] API responses use `ApiResponse` helper
- [ ] New domain events are registered in `EventServiceProvider`
- [ ] Secrets not committed (verify `.gitignore` covers new config files)
