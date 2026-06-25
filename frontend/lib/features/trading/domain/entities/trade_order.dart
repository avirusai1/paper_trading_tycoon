/// Paper Trading Tycoon — TradeOrder Entity
///
/// Domain entity for the trading feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a TradeOrder in the trading module.
abstract class TradeOrder extends Equatable {
  const TradeOrder();
}
