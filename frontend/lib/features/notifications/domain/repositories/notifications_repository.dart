/// Paper Trading Tycoon — Notifications Repository Interface
///
/// Abstract contract for notifications data operations.
/// Implemented in the data layer (data/repositories/).
/// Use cases depend on this interface, never on the concrete implementation.
library;

/// Contract for all notifications data access operations.
abstract interface class AppNotificationRepository {
  // Repository methods are defined per milestone as features are implemented.
  // All methods return Either<Failure, T> from the dartz package.
}
