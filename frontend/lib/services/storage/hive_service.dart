/// Paper Trading Tycoon — Hive Local Storage Service
///
/// Initializes Hive and registers all typed adapters.
/// Called once in main.dart before runApp().
///
/// Box access is provided through typed [HiveBox] wrappers, not via
/// raw Hive.box() calls, to ensure compile-time safety and test isolation.
library;

import 'package:hive_flutter/hive_flutter.dart';

/// Manages Hive initialization and box registration.
abstract final class HiveService {
  /// Initializes Hive and opens all required boxes.
  /// Must be called once before runApp().
  static Future<void> initialize() async {
    await Hive.initFlutter();

    // Register all Hive type adapters here as features are implemented.
    // Example: Hive.registerAdapter(UserProfileModelAdapter());

    // Open all persistent boxes.
    await Future.wait([
      Hive.openBox<dynamic>('user_box'),
      Hive.openBox<dynamic>('feature_flags_box'),
      Hive.openBox<dynamic>('stocks_box'),
      Hive.openBox<dynamic>('watchlist_box'),
      Hive.openBox<dynamic>('preferences_box'),
      Hive.openBox<dynamic>('notifications_box'),
      Hive.openBox<dynamic>('portfolio_box'),
    ]);
  }

  /// Closes all open Hive boxes.
  /// Should be called during app shutdown if needed.
  static Future<void> closeAll() async {
    await Hive.close();
  }

  /// Clears all user data from Hive boxes on logout.
  /// Does not clear preferences (theme, onboarding state).
  static Future<void> clearUserData() async {
    await Future.wait([
      Hive.box<dynamic>('user_box').clear(),
      Hive.box<dynamic>('watchlist_box').clear(),
      Hive.box<dynamic>('notifications_box').clear(),
      Hive.box<dynamic>('portfolio_box').clear(),
    ]);
  }
}
