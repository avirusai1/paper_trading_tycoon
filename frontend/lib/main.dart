/// Paper Trading Tycoon — Application Entry Point
///
/// Bootstraps the Flutter application with:
/// - Firebase initialization
/// - Hive local storage initialization
/// - Riverpod ProviderScope wrapping the root widget
/// - Global error handling for uncaught Flutter and platform errors
library;

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_crashlytics/firebase_crashlytics.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app.dart';
import 'services/storage/hive_service.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Firebase
  await Firebase.initializeApp();

  // Route all Flutter framework errors to Crashlytics in production
  if (!kDebugMode) {
    FlutterError.onError = FirebaseCrashlytics.instance.recordFlutterFatalError;
    PlatformDispatcher.instance.onError = (error, stack) {
      FirebaseCrashlytics.instance.recordError(error, stack, fatal: true);
      return true;
    };
  }

  // Initialize Hive local storage
  await HiveService.initialize();

  runApp(
    const ProviderScope(
      child: PaperTradingTycoonApp(),
    ),
  );
}
