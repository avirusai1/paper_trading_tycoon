/// Paper Trading Tycoon — Storage Keys
///
/// Central registry of all Hive box names and key strings used for
/// local storage. Keeping keys here prevents typos and makes
/// refactoring storage schema straightforward.
library;

/// Hive box names — one per domain area.
abstract final class HiveBoxNames {
  /// Stores the authenticated user's profile data.
  static const String user = 'user_box';

  /// Stores the feature flags snapshot from the last server fetch.
  static const String featureFlags = 'feature_flags_box';

  /// Stores cached stock list and symbol master data.
  static const String stocks = 'stocks_box';

  /// Stores the user's watchlist symbols.
  static const String watchlist = 'watchlist_box';

  /// Stores user display preferences (theme, language).
  static const String preferences = 'preferences_box';

  /// Stores the notification inbox cache.
  static const String notifications = 'notifications_box';

  /// Stores portfolio snapshot for offline display.
  static const String portfolio = 'portfolio_box';
}

/// Keys within Hive boxes.
abstract final class StorageKeys {
  // ── User Box ─────────────────────────────────────────────────────────────

  /// Cached user profile JSON.
  static const String userProfile = 'user_profile';

  /// Current user's ID (also stored in secure storage for auth).
  static const String userId = 'user_id';

  // ── Preferences Box ───────────────────────────────────────────────────────

  /// ThemeMode string: 'system' | 'light' | 'dark'.
  static const String themeMode = 'theme_mode';

  /// Whether the onboarding flow has been completed.
  static const String onboardingCompleted = 'onboarding_completed';

  // ── Feature Flags Box ─────────────────────────────────────────────────────

  /// Feature flags JSON payload from last server sync.
  static const String flagsPayload = 'flags_payload';

  /// Unix timestamp (ms) of the last feature flags fetch.
  static const String flagsLastFetchedAt = 'flags_last_fetched_at';

  // ── Portfolio Box ─────────────────────────────────────────────────────────

  /// Last-known portfolio snapshot JSON for offline display.
  static const String portfolioSnapshot = 'portfolio_snapshot';

  /// Unix timestamp (ms) of the portfolio snapshot.
  static const String portfolioSnapshotAt = 'portfolio_snapshot_at';
}

/// Keys stored in Flutter Secure Storage (encrypted on device).
/// These keys hold sensitive authentication data.
abstract final class SecureStorageKeys {
  /// JWT access token for API authentication.
  static const String accessToken = 'access_token';

  /// JWT refresh token for silent token renewal.
  static const String refreshToken = 'refresh_token';

  /// Expiry timestamp of the access token (ISO 8601 string).
  static const String tokenExpiresAt = 'token_expires_at';

  /// Authenticated user ID — stored securely to prevent tampering.
  static const String userId = 'secure_user_id';
}
