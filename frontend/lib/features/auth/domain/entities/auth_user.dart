/// Paper Trading Tycoon — AuthUser Entity
///
/// Domain entity for the auth feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a AuthUser in the auth module.
abstract class AuthUser extends Equatable {
  const AuthUser();
}
