/// Paper Trading Tycoon — PremiumSubscription Entity
///
/// Domain entity for the premium feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a PremiumSubscription in the premium module.
abstract class PremiumSubscription extends Equatable {
  const PremiumSubscription();
}
