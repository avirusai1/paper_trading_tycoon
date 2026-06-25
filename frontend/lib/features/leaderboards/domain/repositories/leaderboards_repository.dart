/// Paper Trading Tycoon — Leaderboards Repository Interface
///
/// Abstract contract for leaderboards data operations.
/// Implemented in the data layer (data/repositories/).
/// Use cases depend on this interface, never on the concrete implementation.
library;

/// Contract for all leaderboards data access operations.
abstract interface class LeaderboardEntryRepository {
  // Repository methods are defined per milestone as features are implemented.
  // All methods return Either<Failure, T> from the dartz package.
}
