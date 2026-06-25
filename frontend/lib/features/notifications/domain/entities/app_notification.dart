/// Paper Trading Tycoon — AppNotification Entity
///
/// Domain entity for the notifications feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a AppNotification in the notifications module.
abstract class AppNotification extends Equatable {
  const AppNotification();
}
