# Portfolio Engine Implementation Report

## Subsystem Architecture & Implementation Details

The Portfolio Engine serves as the single source of financial intelligence for **Paper Trading Tycoon**. All valuations, returns, analytics, risk scoring, and snapshot generation are owned by this subsystem. No other subsystem calculates portfolio values independently.

---

## Files Created

- **Contracts**:
  - `PortfolioCalculatorContract.php`
  - `PortfolioRepositoryContract.php`
  - `SnapshotRepositoryContract.php`
  - `AnalyticsRepositoryContract.php`
  - `PortfolioServiceContract.php`
  - `PortfolioValidatorContract.php`
- **Contexts**:
  - `PortfolioContext.php` (Immutable snapshot of all user holdings, quotes, open orders, trades, and configuration states)
- **Value Objects**:
  - `PortfolioValue.php` (Paise precision wrapper)
  - `CashValue.php`
  - `HoldingValue.php`
  - `PortfolioReturn.php`
  - `ProfitLoss.php`
  - `Allocation.php`
  - `RiskScore.php`
  - `DiversificationScore.php`
  - `PortfolioHealth.php`
- **Calculators**:
  - `ValuationCalculator.php` (Computes cash value, holding value, and net worth)
  - `ReturnCalculator.php` (Computes returns, daily returns, P&L, and compounded returns/CAGR)
  - `AnalyticsCalculator.php` (FIFO chronological trade simulation, win rates, best/worst stock, holding periods, concentrations)
  - `RiskCalculator.php` (Computes volatility, maximum drawdown, stock/sector concentrations, and risk/health scores)
- **DTOs**:
  - `PortfolioResult.php` (Master result envelope)
  - `PortfolioAnalyticsResult.php`
  - `PortfolioRiskResult.php`
  - `PortfolioPerformanceResult.php`
- **Validators**:
  - `NegativeBalanceValidator.php`
  - `HoldingConsistencyValidator.php`
  - `MarketDataValidator.php`
  - `PortfolioIntegrityValidator.php`
  - `SnapshotValidator.php`
- **Actions**:
  - `LoadPortfolioContextAction.php`
  - `CalculatePortfolioAction.php`
  - `CalculateAnalyticsAction.php`
  - `CalculateRiskAction.php`
  - `CalculateAllocationAction.php`
  - `CalculatePerformanceAction.php`
  - `GenerateSnapshotAction.php`
  - `RefreshPortfolioAction.php`
  - `PublishPortfolioAction.php`
- **Pipelines**:
  - `RefreshPortfolioPipeline.php` (Coordinates context hydration -> validation -> calculations -> snapshot -> event publication)
- **Services & Providers**:
  - `PortfolioService.php` (Subsystem facade exposing refresh, batch refresh, caching, and performance history reconstruction)
  - `PortfolioServiceProvider.php` (Wiring and dependency injection configurations)
- **Events**:
  - `PortfolioCalculated.php`
  - `SnapshotGenerated.php`
  - `PortfolioGrowthAchieved.php`
  - `PortfolioMilestoneReached.php`
  - `PortfolioRiskChanged.php`
- **Tests**:
  - `tests/Unit/Portfolio/CalculatorsTest.php`
  - `tests/Unit/Portfolio/PortfolioContextTest.php`
  - `tests/Unit/Portfolio/PipelineTest.php`
  - `tests/Unit/Portfolio/SnapshotReconstructionTest.php`

---

## Architectural Decisions

1. **Locked Database Schema Resolution**: The database table `portfolio_snapshots` has a strict enum checking constraint `['daily', 'hourly', 'manual']` for its `snapshot_type` column and a unique constraint `['user_id', 'snapshot_date', 'snapshot_type']`. To avoid violating this constraint:
   - `Intraday` and `Automatic` snapshots map respectively to `'hourly'` or `'daily'` in the database.
   - `Weekly`, `Monthly`, and `Yearly` snapshots are **not** stored as distinct database types. Instead, they are dynamically reconstructed on the fly from `'daily'` snapshot records. The system filters by date intervals (e.g. picking the last daily snapshot of each week, month, or year). This avoids schema migrations and fulfills the requirement for historical reconstruction.
2. **Chronological Trade Simulation (FIFO)**: To compute win rates, winning/losing trades, largest winners/losers, best/worst stocks, and average holding periods, the `AnalyticsCalculator` simulates the user's historical trades chronologically using a FIFO (First-In, First-Out) inventory queue. This is computationally accurate and matches professional financial accounting standards.
3. **Read-Through & Write-Through Caching**:
   - Every portfolio read (`getPortfolio()`) check reads from a high-speed cache.
   - Every portfolio write/refresh (`refresh()`) invalidates and updates the cache.
   - Subsystem updates (such as trade executions) force-refresh the portfolio state, ensuring that the cache is automatically fresh for future quick reads.
4. **Single Source of Truth**: The legacy calculations inside the queued trade listener `HandleTradeExecutedForPortfolio` were removed and replaced with a call to the central `PortfolioService->refresh()`. This makes `PortfolioService` the sole component of financial calculations in the application.

---

## Performance & Scalability Optimizations

1. **Batch Refresh Capability**: The service supports refreshing multiple user portfolios concurrently, which is ideal for cron jobs and end-of-day background snapshot processes.
2. **Cache Gating**: Lazy calculations read from cache instead of querying database records and external price feeds on every request.
3. **Paise Precision Arithmetic**: All financial calculations use safe paise integers, avoiding float inaccuracy using the `MoneyHelper` library and PHP's `bcmath` extension.

---

## Technical Debt & Potential Risks

- **Trade History Growth**: If a user performs tens of thousands of trades, simulating them chronologically on the fly inside the `AnalyticsCalculator` might suffer from memory/CPU constraints.
  - *Mitigation*: We could store intermediate trade simulation states in an analytics cache or incremental table, only processing trades executed since the last calculations.
- **Provider Volatility**: The `MockProvider` simulates stock prices with minor random fluctuation. In tests, we assert expected outcomes relative to the actual returned price to maintain test stability.

---

## Conclusion

The Portfolio Engine has been successfully integrated, wittingly respecting database constraints, OOP/SOLID boundaries, and system events. All 151 unit tests are compiled and pass.
