/// Paper Trading Tycoon — Route Names
///
/// All named route constants in one place. Using constants rather than
/// raw strings prevents typos and enables IDE refactoring support.
///
/// Naming convention: module_screenName (snake_case).
library;

abstract final class RouteNames {
  // ── Splash / Onboarding ───────────────────────────────────────────────────

  static const String splash = 'splash';
  static const String onboarding = 'onboarding';

  // ── Authentication ────────────────────────────────────────────────────────

  static const String login = 'login';
  static const String register = 'register';
  static const String emailVerification = 'email_verification';
  static const String forgotPassword = 'forgot_password';
  static const String resetPassword = 'reset_password';

  // ── Main App (Shell) ──────────────────────────────────────────────────────

  static const String home = 'home';
  static const String stockMarket = 'stock_market';
  static const String portfolio = 'portfolio';
  static const String leaderboards = 'leaderboards';
  static const String profile = 'profile';

  // ── Stock Market ──────────────────────────────────────────────────────────

  static const String stockDetail = 'stock_detail';
  static const String stockSearch = 'stock_search';
  static const String watchlist = 'watchlist';

  // ── Trading ───────────────────────────────────────────────────────────────

  static const String buyOrder = 'buy_order';
  static const String sellOrder = 'sell_order';
  static const String orderSuccess = 'order_success';
  static const String tradeHistory = 'trade_history';

  // ── Portfolio ─────────────────────────────────────────────────────────────

  static const String portfolioDetail = 'portfolio_detail';
  static const String holdingDetail = 'holding_detail';

  // ── Gamification ─────────────────────────────────────────────────────────

  static const String gamification = 'gamification';
  static const String levelUp = 'level_up';
  static const String careerTitle = 'career_title';

  // ── Achievements ──────────────────────────────────────────────────────────

  static const String achievements = 'achievements';
  static const String achievementDetail = 'achievement_detail';

  // ── Challenges ───────────────────────────────────────────────────────────

  static const String challenges = 'challenges';

  // ── Leaderboards ─────────────────────────────────────────────────────────

  static const String leagueDetail = 'league_detail';

  // ── Store ────────────────────────────────────────────────────────────────

  static const String store = 'store';
  static const String storeItem = 'store_item';
  static const String inventory = 'inventory';

  // ── Premium ──────────────────────────────────────────────────────────────

  static const String premium = 'premium';
  static const String premiumSuccess = 'premium_success';

  // ── Notifications ────────────────────────────────────────────────────────

  static const String notifications = 'notifications';

  // ── Referral ─────────────────────────────────────────────────────────────

  static const String referral = 'referral';

  // ── Profile ──────────────────────────────────────────────────────────────

  static const String publicProfile = 'public_profile';
  static const String editProfile = 'edit_profile';

  // ── Settings ─────────────────────────────────────────────────────────────

  static const String settings = 'settings';
  static const String notificationPreferences = 'notification_preferences';
  static const String privacySettings = 'privacy_settings';
  static const String accountSettings = 'account_settings';
  static const String deleteAccount = 'delete_account';
}
