/// Paper Trading Tycoon — Stock_market Repository Interface
///
/// Abstract contract for stock_market data operations.
/// Implemented in the data layer (data/repositories/).
/// Use cases depend on this interface, never on the concrete implementation.
library;

/// Contract for all stock_market data access operations.
abstract interface class StockRepository {
  // Repository methods are defined per milestone as features are implemented.
  // All methods return Either<Failure, T> from the dartz package.
}
