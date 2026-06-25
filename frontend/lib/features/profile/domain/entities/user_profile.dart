/// Paper Trading Tycoon — UserProfile Entity
///
/// Domain entity for the profile feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a UserProfile in the profile module.
abstract class UserProfile extends Equatable {
  const UserProfile();
}
