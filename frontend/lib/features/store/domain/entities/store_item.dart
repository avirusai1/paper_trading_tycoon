/// Paper Trading Tycoon — StoreItem Entity
///
/// Domain entity for the store feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a StoreItem in the store module.
abstract class StoreItem extends Equatable {
  const StoreItem();
}
