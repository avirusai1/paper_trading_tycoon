/// Paper Trading Tycoon — Challenge Entity
///
/// Domain entity for the challenges feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a Challenge in the challenges module.
abstract class Challenge extends Equatable {
  const Challenge();
}
