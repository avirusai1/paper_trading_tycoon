/// Paper Trading Tycoon — Application Logger
///
/// Wraps the `logger` package with application-specific configuration.
/// - Debug builds: pretty-printed logs with emoji prefixes.
/// - Release builds: logging is completely disabled to avoid leaking
///   sensitive information and to reduce APK size.
///
/// Never log: passwords, tokens, PII beyond user ID, raw API keys.
library;

import 'package:flutter/foundation.dart';
import 'package:logger/logger.dart';

/// Application logger singleton.
///
/// Import and use via:
/// ```dart
/// import 'package:paper_trading_tycoon/core/utils/logger.dart';
///
/// AppLogger.info('Trade executed', {'symbol': 'RELIANCE', 'qty': 10});
/// ```
abstract final class AppLogger {
  static final Logger _logger = Logger(
    printer: PrettyPrinter(
      methodCount: 2,
      errorMethodCount: 8,
      lineLength: 120,
      colors: true,
      printEmojis: true,
      dateTimeFormat: DateTimeFormat.onlyTimeAndSinceStart,
    ),
    // Disable all logging in release mode to protect sensitive data.
    level: kReleaseMode ? Level.off : Level.debug,
  );

  /// Logs a debug-level message. Use for development tracing.
  static void debug(String message, [dynamic data]) {
    _logger.d(message, error: data);
  }

  /// Logs an info-level business event (trade, registration, level-up).
  static void info(String message, [dynamic data]) {
    _logger.i(message, error: data);
  }

  /// Logs a warning for recoverable issues.
  static void warning(String message, [dynamic data]) {
    _logger.w(message, error: data);
  }

  /// Logs an error with optional stack trace.
  static void error(String message, [dynamic error, StackTrace? stackTrace]) {
    _logger.e(message, error: error, stackTrace: stackTrace);
  }

  /// Logs a fatal error. In production these are forwarded to Crashlytics
  /// via the global error handler in main.dart.
  static void fatal(String message, [dynamic error, StackTrace? stackTrace]) {
    _logger.f(message, error: error, stackTrace: stackTrace);
  }
}
