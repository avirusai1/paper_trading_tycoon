/// Paper Trading Tycoon — Analytics Service
///
/// Thin wrapper around Firebase Analytics for client-side event tracking.
/// All event names and parameters are defined as constants to prevent
/// tracking inconsistencies across the codebase.
///
/// Events tracked here are client-side signals (screen views, button taps,
/// funnel steps). Server-side events (trades, level-ups) are tracked by
/// the Laravel Analytics subscriber listening to domain events.
///
/// Never include PII in event parameters — only anonymised identifiers.
library;

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/utils/logger.dart';

/// Provides the [AnalyticsService] instance.
final analyticsServiceProvider = Provider<AnalyticsService>((ref) {
  return AnalyticsService();
});

/// Client-side analytics event names.
abstract final class AnalyticsEvent {
  // ── Onboarding ──────────────────────────────────────────────────────────
  static const String onboardingStarted = 'onboarding_started';
  static const String onboardingCompleted = 'onboarding_completed';

  // ── Auth ─────────────────────────────────────────────────────────────────
  static const String registrationStarted = 'registration_started';
  static const String registrationCompleted = 'registration_completed';
  static const String loginCompleted = 'login_completed';
  static const String logoutCompleted = 'logout_completed';

  // ── Market ───────────────────────────────────────────────────────────────
  static const String stockDetailViewed = 'stock_detail_viewed';
  static const String stockSearched = 'stock_searched';
  static const String watchlistItemAdded = 'watchlist_item_added';

  // ── Trading ──────────────────────────────────────────────────────────────
  static const String buyOrderInitiated = 'buy_order_initiated';
  static const String sellOrderInitiated = 'sell_order_initiated';
  static const String orderConfirmed = 'order_confirmed';

  // ── Gamification ─────────────────────────────────────────────────────────
  static const String leaderboardViewed = 'leaderboard_viewed';
  static const String achievementViewed = 'achievement_viewed';
  static const String challengeViewed = 'challenge_viewed';

  // ── Premium ───────────────────────────────────────────────────────────────
  static const String paywallViewed = 'paywall_viewed';
  static const String purchaseInitiated = 'purchase_initiated';

  // ── Store ────────────────────────────────────────────────────────────────
  static const String storeViewed = 'store_viewed';
  static const String itemPurchased = 'item_purchased';
}

/// Analytics service for client-side event tracking.
final class AnalyticsService {
  /// Logs a custom analytics event.
  /// Parameters must not contain PII.
  Future<void> logEvent(
    String name, {
    Map<String, dynamic>? parameters,
  }) async {
    AppLogger.debug('Analytics: $name', parameters);
    // TODO(PTT-ANALYTICS): Wire to FirebaseAnalytics.instance.logEvent()
    // once Firebase configuration is complete per Milestone 13.
  }

  /// Records a screen view for funnel analysis.
  Future<void> logScreenView({required String screenName}) async {
    AppLogger.debug('Screen: $screenName');
    // TODO(PTT-ANALYTICS): Wire to FirebaseAnalytics.instance.logScreenView()
  }

  /// Sets the current user ID for session attribution.
  /// Call after successful login; clear on logout.
  Future<void> setUserId(String? userId) async {
    // TODO(PTT-ANALYTICS): Wire to FirebaseAnalytics.instance.setUserId()
  }
}
