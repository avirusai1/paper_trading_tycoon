/// Paper Trading Tycoon — Achievement Entity
///
/// Domain entity for the achievements feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a Achievement in the achievements module.
abstract class Achievement extends Equatable {
  const Achievement();
}
