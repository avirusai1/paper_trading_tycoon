# ADR-004: Monetary Representation & Coin Ledger Model

**Status:** Accepted  
**Date:** June 2025  
**Required before:** Milestone 4 (Trading Engine) and Milestone 8 (Coin Economy)

---

## Context

Two critical decisions needed resolution before implementing financial logic:
1. How to represent monetary values (virtual cash, prices, P&L) without floating-point errors.
2. How to store coin balances without direct mutation (auditability + fraud prevention).

## Decision 1: Monetary Representation

**All monetary values are stored and transmitted as paise integers.**

- ₹1 = 100 paise.
- ₹10,00,000 = 100,000,000 paise (int).
- Database columns: `BIGINT UNSIGNED` — never `DECIMAL` or `FLOAT`.
- PHP calculations: `bcmath` functions — never native float arithmetic.
- API responses: integer paise values — Flutter formats for display.
- Flutter display: `Formatters.paise(int)` converts to `₹` display strings.

**Why not DECIMAL?**  
DECIMAL is correct for accounting, but integer paise + bcmath achieves the same precision with simpler PHP code and no ORM type-casting footguns.

**Why not float?**  
`0.1 + 0.2 ≠ 0.3` in IEEE 754. Portfolio P&L computed as float would accumulate errors at millions of trade records.

## Decision 2: Coin Ledger Model

**Coin balances are derived from append-only ledger transactions, never stored directly.**

```
coin_transactions table:
  id               BIGINT UNSIGNED PK
  user_id          BIGINT UNSIGNED FK
  amount           INT              (positive = credit, negative = debit)
  source_type      VARCHAR(50)      (CoinTransactionSource enum)
  source_id        VARCHAR(100)     (idempotency key: challenge_id, achievement_id, etc.)
  created_at       TIMESTAMP

user_coin_balance (materialized cache, NOT source of truth):
  user_id          BIGINT UNSIGNED PK
  balance_paise    BIGINT UNSIGNED
  last_updated_at  TIMESTAMP
```

**Balance computation:** `SELECT SUM(amount) FROM coin_transactions WHERE user_id = ?`  
**Materialized cache:** Updated atomically in the same DB transaction as the ledger insert.

**Idempotency:** `UNIQUE INDEX (user_id, source_type, source_id)` — duplicate award rejected at DB level.

**Negative balance prevention:** Debit actions validate `computed_balance >= |debit_amount|` before insert (within a row-locking transaction on the materialized cache row).

## Consequences

- Support queries like "show me all coins earned from challenges in June" are trivially answerable.
- Economy rebalancing (e.g. retroactive bonus) is a compensating insert, not a silent balance edit.
- The `bcmath` dependency must be verified in the PHP build on Hostinger.
- Flutter's `Formatters` class handles all paise→INR display conversions centrally.
