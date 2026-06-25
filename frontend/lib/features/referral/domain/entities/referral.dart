/// Paper Trading Tycoon — Referral Entity
///
/// Domain entity for the referral feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a Referral in the referral module.
abstract class Referral extends Equatable {
  const Referral();
}
