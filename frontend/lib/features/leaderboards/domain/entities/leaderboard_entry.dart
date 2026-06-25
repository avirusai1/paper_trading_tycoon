/// Paper Trading Tycoon — LeaderboardEntry Entity
///
/// Domain entity for the leaderboards feature module.
/// Entities contain only business-critical fields and rules.
/// No JSON serialization here — that belongs in the data layer model.
library;

import 'package:equatable/equatable.dart';

/// Domain entity representing a LeaderboardEntry in the leaderboards module.
abstract class LeaderboardEntry extends Equatable {
  const LeaderboardEntry();
}
