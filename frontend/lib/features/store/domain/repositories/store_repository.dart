/// Paper Trading Tycoon — Store Repository Interface
///
/// Abstract contract for store data operations.
/// Implemented in the data layer (data/repositories/).
/// Use cases depend on this interface, never on the concrete implementation.
library;

/// Contract for all store data access operations.
abstract interface class StoreItemRepository {
  // Repository methods are defined per milestone as features are implemented.
  // All methods return Either<Failure, T> from the dartz package.
}
