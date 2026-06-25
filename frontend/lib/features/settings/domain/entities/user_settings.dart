/// Paper Trading Tycoon — UserSettings Entity
///
/// Domain entity for the settings feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a UserSettings in the settings module.
abstract class UserSettings extends Equatable {
  const UserSettings();
}
