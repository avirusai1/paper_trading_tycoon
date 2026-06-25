/// Paper Trading Tycoon — Portfolio Entity
///
/// Domain entity for the portfolio feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a Portfolio in the portfolio module.
abstract class Portfolio extends Equatable {
  const Portfolio();
}
