/// Paper Trading Tycoon — Feature Flag Service
///
/// Fetches feature flags from the server and caches them locally in Hive.
/// Flags are refreshed on every foreground resume and at app startup.
/// The cache is used as a fallback when the network is unavailable.
///
/// Callers use [isEnabled] for boolean flag checks throughout the UI.
/// UI hides feature entry points when a flag is disabled — the API
/// also enforces flags server-side as defense-in-depth.
///
/// Flag keys are defined in [FeatureFlags] to prevent typos.
library;

import 'dart:convert';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:hive_flutter/hive_flutter.dart';

import '../../core/constants/api_constants.dart';
import '../../core/constants/storage_keys.dart';
import '../../core/utils/logger.dart';
import '../api/api_client.dart';

/// Provides the [FeatureFlagService] instance.
final featureFlagServiceProvider = Provider<FeatureFlagService>((ref) {
  return FeatureFlagService(apiClient: ref.watch(apiClientProvider));
});

/// Reactive provider for the flag map. Riverpod notifier updates this
/// after a successful fetch so all flag consumers rebuild automatically.
final featureFlagsProvider = StateProvider<Map<String, dynamic>>((ref) => {});

/// All known feature flag keys. Centralised to prevent magic strings.
abstract final class FeatureFlags {
  static const String cryptoTrading = 'crypto_trading';
  static const String optionsTrading = 'options_trading';
  static const String battlePass = 'battle_pass';
  static const String aiCoach = 'ai_coach';
  static const String copyTrading = 'copy_trading';
  static const String tournaments = 'tournaments';
  static const String advancedAnalytics = 'advanced_analytics';
}

/// Service that fetches and evaluates feature flags.
final class FeatureFlagService {
  FeatureFlagService({required this.apiClient});

  final ApiClient apiClient;

  static const Duration _cacheTtl = Duration(minutes: 15);
  static const String _cacheKey = StorageKeys.flagsPayload;
  static const String _cacheTimestampKey = StorageKeys.flagsLastFetchedAt;

  Box<dynamic> get _box => Hive.box<dynamic>(HiveBoxNames.featureFlags);

  /// Fetches flags from server and updates local cache.
  /// Falls back to cached flags on network failure.
  Future<Map<String, dynamic>> fetchFlags() async {
    try {
      final data = await apiClient.get(ApiConstants.featureFlags) as Map<String, dynamic>?;
      if (data != null) {
        await _cacheFlags(data);
        AppLogger.debug('Feature flags refreshed from server.', data);
        return data;
      }
    } catch (e) {
      AppLogger.warning('Feature flag fetch failed. Using cached flags.', e);
    }

    return _cachedFlags();
  }

  /// Checks whether a flag is currently enabled.
  /// [userId] is provided for user-level overrides (premium gating, rollout %).
  bool isEnabled(
    Map<String, dynamic> flags,
    String key, {
    String? userId,
  }) {
    final value = flags[key];
    if (value == null) return false;
    if (value is bool) return value;
    // 'premium' flag value means the feature requires an active subscription.
    // The UI should use the premium status provider for these checks.
    if (value is String && value == 'premium') return false;
    return false;
  }

  Future<void> _cacheFlags(Map<String, dynamic> flags) async {
    await _box.put(_cacheKey, jsonEncode(flags));
    await _box.put(_cacheTimestampKey, DateTime.now().millisecondsSinceEpoch);
  }

  Map<String, dynamic> _cachedFlags() {
    final raw = _box.get(_cacheKey) as String?;
    if (raw == null) return _defaultFlags();
    try {
      return jsonDecode(raw) as Map<String, dynamic>;
    } catch (_) {
      return _defaultFlags();
    }
  }

  /// Fallback defaults — all optional features disabled.
  Map<String, dynamic> _defaultFlags() => {
    FeatureFlags.cryptoTrading: false,
    FeatureFlags.optionsTrading: false,
    FeatureFlags.battlePass: false,
    FeatureFlags.aiCoach: false,
    FeatureFlags.copyTrading: false,
    FeatureFlags.tournaments: false,
    FeatureFlags.advancedAnalytics: 'premium',
  };
}
