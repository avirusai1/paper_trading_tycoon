# Reward Engine — Implementation Report

**Subsystem:** `backend/app/RewardEngine/`
**Architecture:** Clean Architecture, Strategy Pattern, Pipeline Pattern, Open/Closed Principle
**PHP Version:** 8.3 (`final`, `readonly`, `declare(strict_types=1)` everywhere)
**Author:** Paper Trading Tycoon — Lead Gameplay Systems Engineering
**Status:** Production-ready skeleton

---

## Overview

The Reward Engine is a fully isolated subsystem responsible for calculating and distributing every reward type in Paper Trading Tycoon — XP, coins, inventory items, badges, career titles, and future reward types. It integrates with the Game Engine via defined contracts and has zero knowledge of HTTP, controllers, Flutter, trading, or authentication.

---

## Directory Structure

```
backend/app/RewardEngine/
├── Actions/
│   ├── DistributeRewardAction.php      # Select strategy → calculate → distribute
│   ├── RecordRewardHistoryAction.php   # Write append-only reward_history audit log
│   └── RollbackRewardAction.php        # Reverse a distributed reward
├── Calculators/
│   ├── CoinCalculator.php              # Coin amounts (paise) with multipliers via bcmath
│   ├── MultiplierResolver.php          # Stacks premium/weekend/season/item multipliers
│   ├── PremiumBonusCalculator.php      # Premium-specific coin bonus
│   ├── ReferralBonusCalculator.php     # Referrer + referred XP/coin amounts
│   ├── SeasonBonusCalculator.php       # Season-end rank-band reward lookup
│   └── XPCalculator.php               # XP with multipliers from Rules Engine
├── Contexts/
│   └── RewardContext.php               # Immutable player state snapshot (built once per run)
├── Contracts/
│   ├── MultiplierResolverContract.php  # Multiplier resolution interface
│   ├── RewardCalculatorContract.php    # Calculator interface
│   ├── RewardContextBuilderContract.php# Context factory interface
│   ├── RewardDistributorContract.php   # Distributor interface (per type)
│   ├── RewardEngineContract.php        # Primary public entry point
│   ├── RewardStrategyContract.php      # Per-type strategy: calculate + distribute + rollback
│   ├── RewardStrategyRegistryContract.php # Strategy lookup
│   └── RewardValidatorContract.php     # Single validation rule interface
├── Distributors/
│   ├── CareerDistributor.php           # Updates user_levels.career_title
│   ├── CoinDistributor.php             # Credits coin_transactions via CoinLedgerContract
│   ├── InventoryDistributor.php        # Creates UserInventory records
│   └── XPDistributor.php              # Grants XP via XPProcessorContract
├── DTOs/
│   ├── CalculatedReward.php            # Output of calculator stage (no DB writes yet)
│   ├── DistributionResult.php          # Output of a single distributor
│   ├── RewardBatchResult.php           # Aggregate of multiple pipeline runs
│   ├── RewardEngineResult.php          # Final result returned to callers
│   ├── RewardRequest.php               # Immutable input to the pipeline
│   └── StrategyResult.php             # Output of a strategy's distribute/rollback
├── Enums/
│   ├── MultiplierType.php              # Multiplier categories with rule key helpers
│   ├── RewardSource.php               # Game system that originated the reward
│   ├── RewardStatus.php               # Pipeline lifecycle status
│   ├── RewardType.php                 # Every reward type (extensible)
│   └── ValidationFailureReason.php    # Machine-readable validation failure codes
├── Events/
│   ├── RewardCalculated.php           # Fired after calculation, before distribution
│   ├── RewardDistributed.php          # Fired by each distributor after DB write
│   ├── RewardEngineEvent.php          # Base class with occurredAt timestamp
│   ├── RewardFailed.php               # Fired on any pipeline failure
│   ├── RewardGranted.php              # Fired after all stages succeed
│   ├── RewardRolledBack.php           # Fired after successful rollback
│   └── RewardValidated.php            # Fired after validator chain passes
├── Exceptions/
│   ├── RewardCalculationException.php # Missing rule key, negative amount
│   ├── RewardDistributionException.php# Insufficient balance, item unavailable
│   ├── RewardEngineException.php      # Root exception (extends DomainException)
│   ├── RewardRollbackException.php    # Partial or complete rollback failure
│   └── RewardValidationException.php  # Typed validators with factory methods
├── Factories/
│   ├── RewardContextFactory.php       # Builds RewardContext (8 DB queries upfront)
│   └── RewardRequestFactory.php       # Convenience constructors for common grants
├── Pipelines/
│   └── RewardPipeline.php             # Orchestrates all 5 stages with rollback
├── Services/
│   └── RewardEngine.php               # Central singleton service (public API)
├── Strategies/
│   ├── BadgeRewardStrategy.php        # Badge-type inventory grants
│   ├── CareerRewardStrategy.php       # Career title unlocks
│   ├── CoinRewardStrategy.php         # Coin credits via CoinLedgerContract
│   ├── InventoryRewardStrategy.php    # Generic store item grants
│   └── XPRewardStrategy.php          # XP grants via XPProcessorContract
├── Support/
│   └── RewardStrategyRegistry.php    # Runtime strategy lookup map
└── Validators/
    ├── DailyLimitValidator.php        # Cap on per-source daily reward count
    ├── DuplicateRewardValidator.php   # Blocks already-recorded idempotency keys
    ├── ExpiredRewardValidator.php     # Blocks rewards past their expiry timestamp
    ├── FeatureGateValidator.php       # Blocks disabled reward types via feature flags
    ├── PremiumOnlyValidator.php       # Blocks non-premium users from premium rewards
    ├── ReferralAbuseValidator.php     # Self-referral, fraud flag, record existence
    ├── SeasonValidityValidator.php    # Requires active season for season rewards
    └── UserStatusValidator.php        # Blocks banned/suspended users
```

**Total: 57 files**

---

## Pipeline Lifecycle

```
RewardRequest
     │
     ▼
[UserStatusValidator]      ← Banned users blocked
[FeatureGateValidator]     ← Disabled reward types blocked
[DuplicateRewardValidator] ← Idempotency pre-check
[PremiumOnlyValidator]     ← Premium gate
[DailyLimitValidator]      ← Per-source daily cap
[SeasonValidityValidator]  ← Season rewards require active season
[ExpiredRewardValidator]   ← Time-limited reward gate
[ReferralAbuseValidator]   ← Referral fraud checks
     │
     ▼ RewardValidated event
     │
[XPCalculator / CoinCalculator / SeasonBonusCalculator / ...]
     │
     ▼ RewardCalculated event
     │
[XPRewardStrategy / CoinRewardStrategy / InventoryRewardStrategy / ...]
     │  ↳ strategy.calculate() + strategy.distribute()
     │
     ▼ RewardDistributed event
     │
[RecordRewardHistoryAction] → reward_history (append-only)
     │
     ▼ RewardGranted event
     │
RewardEngineResult (returned to caller)
```

On failure at any post-validation stage: RollbackRewardAction fires → RewardRolledBack event.

---

## Integration with Game Engine

| Game Engine Contract     | Used by                                       |
|--------------------------|-----------------------------------------------|
| `GameRuleProviderContract` | All calculators — zero hardcoded values      |
| `CoinLedgerContract`       | CoinDistributor, CoinRewardStrategy          |
| `XPProcessorContract`      | XPDistributor, XPRewardStrategy              |

The Reward Engine never writes to `xp_logs` or `coin_transactions` directly. All writes go through the Game Engine contracts, preserving the audit trail and level-up detection built there.

---

## Key Design Decisions

### 1. Paise Integer Model (ADR-004 compliance)
All coin amounts are BIGINT paise (₹1 = 100 paise). `CoinCalculator` uses `bcmath` for multiplier application to avoid float rounding on large amounts. Never stores or returns floats for monetary values.

### 2. Idempotency at Two Layers
- **Pre-check:** `DuplicateRewardValidator` queries `reward_history` before distributing.
- **DB-level:** `UniqueConstraintViolationException` caught in distributors and history recorder for silent no-op on race conditions.

### 3. Open/Closed for New Reward Types
Adding a new `RewardType` requires:
1. New enum case in `RewardType.php`
2. New `RewardStrategyContract` implementation
3. Registration in `RewardStrategyRegistry` via `AppServiceProvider`

The pipeline, validators, calculators, and distributors require **no changes**.

### 4. Zero Hardcoded Game Values
Every numeric game constant (XP amounts, coin amounts, multipliers, daily limits) is fetched from `GameRuleProviderContract`. Calculator rule keys follow the convention `rewards.{type}.{source}`.

### 5. Rollback Support
`RollbackRewardAction` looks up the original `reward_history` record, determines the `RewardType`, and calls `strategy.rollback()`. Ledger-based types (XP, coins) issue compensating transactions. Inventory/badge types delete the granted records. Career titles are non-reversible by game design.

### 6. Dry Run Support
`RewardRequest::dryRun = true` runs the full pipeline (including validation and calculation) but suppresses all DB writes. Used for reward preview in the UI.

---

## AppServiceProvider Wiring

The full DI graph is registered in `AppServiceProvider::register()`:

- **Singletons:** `MultiplierResolver`, `RewardStrategyRegistry`, `RewardContextFactory`, `RewardPipeline`, `RewardEngine`
- **Transients:** All calculators, strategies, validators, actions

The validator order in the pipeline is intentional: fast/cheap checks (user status, feature gate) run before slow checks (daily limit DB query, referral DB query).

---

## Unit Tests (7 Skeletons)

Located in `tests/Unit/RewardEngine/`:

| File | Coverage Area |
|------|---------------|
| `RewardPipelineTest.php` | Pipeline orchestration, validator chain, dry run |
| `DuplicateRewardValidatorTest.php` | Duplicate detection, admin bypass |
| `MultiplierResolverTest.php` | Multiplier stacking, floor at 1.0 |
| `XPCalculatorTest.php` | Base XP, multipliers, override, missing rule |
| `CoinCalculatorTest.php` | Paise amounts, bcmath, missing rule |
| `RewardRequestTest.php` | Idempotency key format, dry run copy, metadata |
| `RewardStrategyRegistryTest.php` | Strategy lookup, missing strategy, runtime registration |

---

## Security Constraints

- No HTTP, controller, Flutter, API, or trading knowledge anywhere in the subsystem
- No authentication or session state accessed
- PII logged only as `user_id` (integer)
- Admin grants bypass validators but are still audit-logged to `reward_history`
- Referral abuse detection is synchronous fast-path; ML-based scoring is async via Jobs

---

## Future Extensions

The following reward types have enum cases defined and are ready for strategy implementation with zero pipeline changes:

- `RewardType::Avatar`, `RewardType::Frame`, `RewardType::Theme` — cosmetic store items
- `RewardType::MissionUnlock` — unlocks a new mission tier
- `RewardType::FeatureUnlock` — grants access to a premium feature
- `RewardType::Title` — title-only grants (not tied to career levels)
- `RewardType::Event` — time-limited event reward with expiry

---

*Generated by Claude — Paper Trading Tycoon Gameplay Systems Engineering*
