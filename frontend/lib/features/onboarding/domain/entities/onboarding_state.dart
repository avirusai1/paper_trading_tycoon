/// Paper Trading Tycoon — OnboardingState Entity
///
/// Domain entity for the onboarding feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a OnboardingState in the onboarding module.
abstract class OnboardingState extends Equatable {
  const OnboardingState();
}
