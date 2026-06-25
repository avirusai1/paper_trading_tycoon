/// Paper Trading Tycoon — HomeState Entity
///
/// Domain entity for the home feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a HomeState in the home module.
abstract class HomeState extends Equatable {
  const HomeState();
}
