/// Paper Trading Tycoon — Stock Entity
///
/// Domain entity for the stock_market feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a Stock in the stock_market module.
abstract class Stock extends Equatable {
  const Stock();
}
