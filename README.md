# Paper Trading Tycoon

A gamified Indian stock market simulator. Practice trading with ₹10,00,000 virtual cash, earn XP, climb leagues, and complete challenges — with zero real-money risk.

---

## Architecture

**Frontend:** Flutter (iOS + Android) using Clean Architecture and Riverpod state management.  
**Backend:** Laravel 12 REST API with domain event architecture.  
**Database:** MySQL 8.0.  
**Infrastructure:** Hostinger shared hosting (V1).

See `docs/adr/001-tech-stack.md` for full rationale.

---

## Repository Layout

```
paper_trading_tycoon/
├── frontend/       # Flutter app
├── backend/        # Laravel 12 API
├── docs/           # Architecture docs and ADRs
└── .github/        # CI/CD workflows
```

---

## Prerequisites

**Flutter:**
- Flutter SDK ≥ 3.22 (stable channel)
- Dart ≥ 3.4
- Android SDK / Xcode for device targets

**Backend:**
- PHP 8.3 with extensions: `mbstring`, `pdo_mysql`, `bcmath`
- Composer 2.x
- MySQL 8.0
- Node.js (for asset compilation if needed)

---

## Local Setup

### Backend

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed          # seeds rules engine defaults
php artisan serve            # http://localhost:8000
```

**Required `.env` values** (see `.env.example` for all keys):
- `DB_*` — MySQL connection
- `SANCTUM_STATEFUL_DOMAINS` — set to `localhost` for local dev
- `MARKET_DATA_API_KEY` — Twelve Data API key (get a free key at twelvedata.com)
- `FIREBASE_*` — Firebase project credentials for push notifications

### Flutter

```bash
cd frontend
flutter pub get
dart run build_runner build --delete-conflicting-outputs
flutter run
```

Create `frontend/.env` with:
```
API_BASE_URL=http://localhost:8000
```

**Note:** Firebase config files (`google-services.json`, `GoogleService-Info.plist`) are gitignored. Download from Firebase Console and place in:
- Android: `frontend/android/app/google-services.json`
- iOS: `frontend/ios/Runner/GoogleService-Info.plist`

---

## Running Tests

```bash
# Flutter
cd frontend
flutter test --coverage

# Laravel
cd backend
php artisan test --parallel
vendor/bin/phpstan analyse
vendor/bin/pint --test
```

---

## Environment Variables

All required keys are documented in `backend/.env.example`. No secrets are committed to this repository. Feature flags default to `false` — set `FLAG_*=true` in `.env` to enable.

---

## Documentation

| Document | Description |
|----------|-------------|
| `docs/CODING_GUIDELINES.md` | Naming conventions, patterns, forbidden practices |
| `docs/FOLDER_STRUCTURE.md` | Canonical directory layout with rationale |
| `docs/DEPENDENCY_GUIDE.md` | Why each dependency was chosen |
| `docs/adr/001-tech-stack.md` | Technology stack decisions |
| `docs/adr/002-market-data-provider.md` | Market data provider evaluation (pending decision) |
| `docs/adr/003-domain-event-bus.md` | Event delivery and queue partitioning strategy |
| `docs/adr/004-coin-ledger-model.md` | Monetary representation and coin ledger design |

---

## CI/CD

GitHub Actions runs on every push and PR to `main` / `develop`:

- **Flutter CI** (`.github/workflows/flutter-ci.yml`): pub get → build_runner → format check → analyze → test
- **Laravel CI** (`.github/workflows/laravel-ci.yml`): PHPStan → Pint → PHPUnit (MySQL service container)

Both pipelines must be green before any merge to `main`.

---

## Security

- Secrets are never committed. Use `.env` (gitignored) locally; GitHub Secrets in CI.
- Flutter calls the Laravel API only. Provider API keys never leave the backend.
- Passwords, tokens, and PII are never logged.
- All monetary values are paise integers processed with `bcmath` (no float arithmetic).

---

## Contributing

See `docs/CODING_GUIDELINES.md` for code standards, naming conventions, and the PR checklist.

Branch naming: `feature/PTT-{ticket}-description` or `fix/PTT-{ticket}-description`.
