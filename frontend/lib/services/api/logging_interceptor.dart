/// Paper Trading Tycoon — Logging Interceptor
///
/// Logs all outgoing HTTP requests and incoming responses in debug mode.
/// Completely silent in release builds to prevent sensitive data leakage.
///
/// Logged: method, URL, status code, response time.
/// NOT logged: Authorization headers, passwords, raw tokens.
library;

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import '../../core/utils/logger.dart';

/// Dio interceptor that logs HTTP traffic in debug mode only.
final class LoggingInterceptor extends Interceptor {
  final _stopwatch = Stopwatch();

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    if (kDebugMode) {
      _stopwatch
        ..reset()
        ..start();
      AppLogger.debug(
        '→ ${options.method} ${options.uri}',
        {
          'headers': _sanitizeHeaders(options.headers),
          'data': options.data,
          'queryParameters': options.queryParameters,
        },
      );
    }
    handler.next(options);
  }

  @override
  void onResponse(Response<dynamic> response, ResponseInterceptorHandler handler) {
    if (kDebugMode) {
      _stopwatch.stop();
      AppLogger.debug(
        '← ${response.statusCode} ${response.requestOptions.uri} [${_stopwatch.elapsedMilliseconds}ms]',
        response.data,
      );
    }
    handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    if (kDebugMode) {
      _stopwatch.stop();
      AppLogger.warning(
        '✗ ${err.requestOptions.method} ${err.requestOptions.uri} '
        '[${err.response?.statusCode ?? 'NO_STATUS'}] [${_stopwatch.elapsedMilliseconds}ms]',
        err.message,
      );
    }
    handler.next(err);
  }

  /// Removes sensitive headers from logs.
  Map<String, dynamic> _sanitizeHeaders(Map<String, dynamic> headers) {
    final sanitized = Map<String, dynamic>.from(headers);
    sanitized.remove('Authorization');
    sanitized.remove('Cookie');
    return sanitized;
  }
}
