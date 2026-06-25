/// Paper Trading Tycoon — Preference Manager
///
/// Manages user display preferences stored in Hive.
/// Handles non-sensitive settings: theme mode, onboarding state.
/// Exposed via Riverpod providers so the UI reactively updates on changes.
library;

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:hive_flutter/hive_flutter.dart';

import '../../core/constants/storage_keys.dart';
import '../../core/utils/logger.dart';

/// Provides the [PreferenceManager] instance.
final preferenceManagerProvider = Provider<PreferenceManager>((ref) {
  return PreferenceManager();
});

/// Provider for the current [ThemeMode] read from Hive preferences.
/// Used in app.dart to hydrate the [themeModeProvider] on startup.
final savedThemeModeProvider = FutureProvider<ThemeMode>((ref) async {
  return ref.watch(preferenceManagerProvider).getThemeMode();
});

/// Manages user preference persistence.
final class PreferenceManager {
  Box<dynamic> get _box => Hive.box<dynamic>(HiveBoxNames.preferences);

  // ── Theme ─────────────────────────────────────────────────────────────────

  /// Persists the user's chosen [ThemeMode].
  Future<void> setThemeMode(ThemeMode mode) async {
    await _box.put(StorageKeys.themeMode, mode.name);
    AppLogger.debug('Theme mode saved: ${mode.name}');
  }

  /// Reads the stored [ThemeMode], defaulting to [ThemeMode.system].
  ThemeMode getThemeMode() {
    final value = _box.get(StorageKeys.themeMode) as String?;
    return switch (value) {
      'light' => ThemeMode.light,
      'dark' => ThemeMode.dark,
      _ => ThemeMode.system,
    };
  }

  // ── Onboarding ────────────────────────────────────────────────────────────

  /// Marks the onboarding flow as completed so it never shows again.
  Future<void> setOnboardingCompleted() async {
    await _box.put(StorageKeys.onboardingCompleted, true);
  }

  /// Whether the user has completed the onboarding flow.
  bool get isOnboardingCompleted {
    return _box.get(StorageKeys.onboardingCompleted, defaultValue: false) as bool;
  }
}
