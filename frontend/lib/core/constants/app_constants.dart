/// Paper Trading Tycoon — Application Constants
///
/// Central registry of all compile-time application constants.
/// These values are product decisions, not environment-specific config.
/// Environment-specific values (API URLs, keys) live in core/env/.
library;

/// Top-level application constants.
abstract final class AppConstants {
  /// Virtual cash granted to every new user on registration (in paise).
  /// ₹10,00,000 = 1,000,000,00 paise.
  /// All monetary values are stored and transmitted as integers (paise)
  /// to eliminate floating-point precision errors. See ADR-004.
  static const int startingVirtualCashPaise = 100000000;

  /// Human-readable starting cash amount for display purposes.
  static const String startingCashDisplay = '₹10,00,000';

  /// Application name used across UI and metadata.
  static const String appName = 'Paper Trading Tycoon';

  /// Short name for compact UI spaces.
  static const String appNameShort = 'PTT';

  /// Minimum build version required to use the app.
  /// Users on older builds are prompted to update.
  static const int minimumSupportedBuildNumber = 1;

  /// Default pagination page size for all list endpoints.
  static const int defaultPageSize = 20;

  /// Maximum watchlist items for a free-tier user (Level 1–5).
  static const int freeWatchlistLimit = 10;

  /// Maximum watchlist items added at Intern Trader tier (Level 6–10).
  static const int internWatchlistBonus = 2;

  /// Session inactivity timeout in minutes before prompting re-auth.
  static const int sessionInactivityMinutes = 30;

  /// Minimum search query length before triggering a stock search.
  static const int minSearchQueryLength = 2;

  /// Debounce duration (ms) for search inputs to avoid excessive API calls.
  static const int searchDebounceDurationMs = 400;

  /// Maximum retry attempts for failed network requests.
  static const int maxNetworkRetryAttempts = 3;

  /// Duration (seconds) for animated transitions in level-up celebrations.
  static const int levelUpAnimationDurationSeconds = 3;
}
