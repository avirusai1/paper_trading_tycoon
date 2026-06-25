/// Paper Trading Tycoon — Premium Repository Interface
///
/// Abstract contract for premium data operations.
/// Implemented in the data layer (data/repositories/).
/// Use cases depend on this interface, never on the concrete implementation.
library;

/// Contract for all premium data access operations.
abstract interface class PremiumSubscriptionRepository {
  // Repository methods are defined per milestone as features are implemented.
  // All methods return Either<Failure, T> from the dartz package.
}
