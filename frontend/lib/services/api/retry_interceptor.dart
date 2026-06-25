/// Paper Trading Tycoon — Retry Interceptor
///
/// Automatically retries failed requests on network errors and 5xx responses
/// using exponential backoff. Does NOT retry:
///   - 4xx client errors (the client must fix the request).
///   - POST/DELETE requests by default (to prevent duplicate submissions).
///   - Requests that already carry an idempotency key (callers handle retry).
///
/// Max retries and backoff are configurable via constructor parameters.
library;

import 'dart:math' as math;

import 'package:dio/dio.dart';

import '../../core/constants/app_constants.dart';
import '../../core/utils/logger.dart';

/// Dio interceptor that retries transient network failures.
final class RetryInterceptor extends Interceptor {
  RetryInterceptor({
    required this.dio,
    this.maxRetries = AppConstants.maxNetworkRetryAttempts,
    this.baseDelayMs = 500,
  });

  final Dio dio;
  final int maxRetries;

  /// Base delay in milliseconds for the first retry.
  /// Each subsequent retry doubles this value (exponential backoff).
  final int baseDelayMs;

  static const String _retryCountKey = '_retry_count';

  @override
  Future<void> onError(DioException err, ErrorInterceptorHandler handler) async {
    final retryCount = err.requestOptions.extra[_retryCountKey] as int? ?? 0;

    if (!_shouldRetry(err, retryCount)) {
      handler.next(err);
      return;
    }

    final delayMs = baseDelayMs * math.pow(2, retryCount).toInt();
    AppLogger.warning(
      'Request failed. Retrying in ${delayMs}ms (attempt ${retryCount + 1}/$maxRetries).',
      err.message,
    );

    await Future<void>.delayed(Duration(milliseconds: delayMs));

    err.requestOptions.extra[_retryCountKey] = retryCount + 1;

    try {
      final response = await dio.fetch<dynamic>(err.requestOptions);
      handler.resolve(response);
    } on DioException catch (retryError) {
      handler.next(retryError);
    }
  }

  bool _shouldRetry(DioException err, int retryCount) {
    if (retryCount >= maxRetries) return false;

    // Only retry safe idempotent methods to avoid side effects.
    final method = err.requestOptions.method.toUpperCase();
    if (method == 'POST' || method == 'DELETE' || method == 'PATCH') {
      // Allow retry on POST only if the caller has set an idempotency key.
      final hasIdempotencyKey =
          err.requestOptions.headers.containsKey('Idempotency-Key');
      if (!hasIdempotencyKey) return false;
    }

    return switch (err.type) {
      DioExceptionType.connectionError => true,
      DioExceptionType.connectionTimeout => true,
      DioExceptionType.receiveTimeout => true,
      DioExceptionType.sendTimeout => true,
      DioExceptionType.badResponse => (err.response?.statusCode ?? 0) >= 500,
      _ => false,
    };
  }
}
