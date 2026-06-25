/// Paper Trading Tycoon — API Constants
///
/// Centralises all API endpoint paths and HTTP configuration values.
/// The base URL is injected from environment config (core/env/) and must
/// never be hardcoded here. Endpoint paths are versioned under /api/v1/.
library;

/// API endpoint paths and HTTP configuration constants.
///
/// Usage:
///   dio.get('${env.apiBaseUrl}${ApiConstants.stockQuote}/$symbol')
abstract final class ApiConstants {
  // ── API versioning ────────────────────────────────────────────────────────

  static const String apiVersion = '/api/v1';

  // ── Authentication ────────────────────────────────────────────────────────

  static const String register = '$apiVersion/auth/register';
  static const String login = '$apiVersion/auth/login';
  static const String logout = '$apiVersion/auth/logout';
  static const String refreshToken = '$apiVersion/auth/refresh';
  static const String verifyEmail = '$apiVersion/auth/email/verify';
  static const String resendVerification = '$apiVersion/auth/email/resend';
  static const String forgotPassword = '$apiVersion/auth/password/forgot';
  static const String resetPassword = '$apiVersion/auth/password/reset';

  // ── User Profile ──────────────────────────────────────────────────────────

  static const String profile = '$apiVersion/profile';
  static const String updateProfile = '$apiVersion/profile';
  static const String publicProfile = '$apiVersion/users';

  // ── Stock Market ──────────────────────────────────────────────────────────

  static const String stocks = '$apiVersion/stocks';
  static const String stockQuote = '$apiVersion/stocks/quote';
  static const String stockSearch = '$apiVersion/stocks/search';
  static const String marketStatus = '$apiVersion/stocks/market-status';
  static const String trendingStocks = '$apiVersion/stocks/trending';

  // ── Watchlist ─────────────────────────────────────────────────────────────

  static const String watchlist = '$apiVersion/watchlist';

  // ── Trading ───────────────────────────────────────────────────────────────

  static const String buyOrder = '$apiVersion/trades/buy';
  static const String sellOrder = '$apiVersion/trades/sell';
  static const String tradeHistory = '$apiVersion/trades/history';

  // ── Portfolio ─────────────────────────────────────────────────────────────

  static const String portfolio = '$apiVersion/portfolio';
  static const String holdings = '$apiVersion/portfolio/holdings';
  static const String portfolioHistory = '$apiVersion/portfolio/history';

  // ── Gamification ─────────────────────────────────────────────────────────

  static const String gameState = '$apiVersion/game/state';
  static const String dailyLogin = '$apiVersion/game/daily-login';

  // ── Achievements ──────────────────────────────────────────────────────────

  static const String achievements = '$apiVersion/achievements';

  // ── Challenges ───────────────────────────────────────────────────────────

  static const String challenges = '$apiVersion/challenges';
  static const String claimChallenge = '$apiVersion/challenges/claim';

  // ── Leaderboards ─────────────────────────────────────────────────────────

  static const String leaderboards = '$apiVersion/leaderboards';

  // ── Coin Economy ─────────────────────────────────────────────────────────

  static const String coinBalance = '$apiVersion/economy/balance';
  static const String coinTransactions = '$apiVersion/economy/transactions';

  // ── Store ────────────────────────────────────────────────────────────────

  static const String storeCatalog = '$apiVersion/store/catalog';
  static const String storePurchase = '$apiVersion/store/purchase';
  static const String storeInventory = '$apiVersion/store/inventory';

  // ── Premium ──────────────────────────────────────────────────────────────

  static const String premiumPlans = '$apiVersion/premium/plans';
  static const String premiumVerify = '$apiVersion/premium/verify';
  static const String premiumStatus = '$apiVersion/premium/status';

  // ── Notifications ────────────────────────────────────────────────────────

  static const String notificationInbox = '$apiVersion/notifications';
  static const String notificationPreferences = '$apiVersion/notifications/preferences';
  static const String registerPushToken = '$apiVersion/notifications/token';

  // ── Referral ─────────────────────────────────────────────────────────────

  static const String referralCode = '$apiVersion/referral/code';
  static const String referralHistory = '$apiVersion/referral/history';

  // ── Feature Flags ────────────────────────────────────────────────────────

  static const String featureFlags = '$apiVersion/feature-flags';

  // ── System ───────────────────────────────────────────────────────────────

  static const String healthCheck = '/api/health';

  // ── HTTP Configuration ───────────────────────────────────────────────────

  /// Default connection timeout in milliseconds.
  static const int connectTimeoutMs = 15000;

  /// Default receive timeout in milliseconds.
  static const int receiveTimeoutMs = 15000;

  /// Send timeout in milliseconds (for upload/POST requests).
  static const int sendTimeoutMs = 15000;
}
