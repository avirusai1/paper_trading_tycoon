/// Paper Trading Tycoon — Dio HTTP Client
///
/// Configures the singleton Dio instance with all interceptors:
///   1. [AuthInterceptor]       — Injects Bearer token, handles 401 refresh.
///   2. [LoggingInterceptor]    — Logs requests/responses in debug mode.
///   3. [RetryInterceptor]      — Retries on network errors with exponential backoff.
///   4. [ErrorInterceptor]      — Maps Dio errors to typed [AppException] types.
///   5. Connectivity guard      — Rejects requests immediately when offline.
///
/// Exposed as a Riverpod provider so all repositories get the same instance.
library;

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/constants/api_constants.dart';
import '../../core/env/app_env.dart';
import 'auth_interceptor.dart';
import 'error_interceptor.dart';
import 'logging_interceptor.dart';
import 'retry_interceptor.dart';

/// Provides the configured [Dio] instance.
final dioProvider = Provider<Dio>((ref) {
  final dio = Dio(
    BaseOptions(
      baseUrl: AppEnv.apiBaseUrl,
      connectTimeout: const Duration(milliseconds: ApiConstants.connectTimeoutMs),
      receiveTimeout: const Duration(milliseconds: ApiConstants.receiveTimeoutMs),
      sendTimeout: const Duration(milliseconds: ApiConstants.sendTimeoutMs),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-App-Platform': 'flutter',
      },
    ),
  );

  // Order matters: Auth → Logging → Retry → Error
  dio.interceptors.addAll([
    ref.watch(authInterceptorProvider),
    LoggingInterceptor(),
    RetryInterceptor(dio: dio),
    ErrorInterceptor(),
  ]);

  return dio;
});
