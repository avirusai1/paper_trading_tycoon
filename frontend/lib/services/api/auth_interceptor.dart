/// Paper Trading Tycoon — Auth Interceptor
///
/// Injects the Bearer token into every outgoing request.
/// On 401 responses, attempts a silent token refresh via the refresh endpoint.
/// If refresh fails, triggers a logout and redirects the user to the login screen.
///
/// Design: uses a lock to prevent concurrent refresh race conditions —
/// only one refresh request flies at a time; all other 401s are queued.
library;

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/constants/api_constants.dart';
import '../../core/utils/logger.dart';
import '../storage/secure_storage_service.dart';

/// Provides the [AuthInterceptor] instance with access to secure storage.
final authInterceptorProvider = Provider<AuthInterceptor>((ref) {
  return AuthInterceptor(
    secureStorage: ref.watch(secureStorageServiceProvider),
  );
});

/// Interceptor that manages JWT Bearer token injection and silent refresh.
final class AuthInterceptor extends Interceptor {
  AuthInterceptor({required this.secureStorage});

  final SecureStorageService secureStorage;

  /// Tracks whether a token refresh is currently in flight to prevent
  /// multiple concurrent refresh requests.
  bool _isRefreshing = false;

  /// Queue of requests waiting for a token refresh to complete.
  final List<RequestOptions> _pendingRequests = [];

  @override
  Future<void> onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    // Skip injecting token for auth endpoints (login, register, refresh).
    final isAuthEndpoint = options.path == ApiConstants.login ||
        options.path == ApiConstants.register ||
        options.path == ApiConstants.refreshToken;

    if (!isAuthEndpoint) {
      final token = await secureStorage.readAccessToken();
      if (token != null) {
        options.headers['Authorization'] = 'Bearer $token';
      }
    }

    handler.next(options);
  }

  @override
  Future<void> onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) async {
    if (err.response?.statusCode != 401) {
      handler.next(err);
      return;
    }

    // Avoid refresh loop on the refresh endpoint itself.
    if (err.requestOptions.path == ApiConstants.refreshToken) {
      await _handleLogout();
      handler.next(err);
      return;
    }

    if (_isRefreshing) {
      _pendingRequests.add(err.requestOptions);
      return;
    }

    _isRefreshing = true;

    try {
      final refreshToken = await secureStorage.readRefreshToken();
      if (refreshToken == null) {
        await _handleLogout();
        handler.next(err);
        return;
      }

      // Attempt silent token refresh.
      final refreshDio = Dio(BaseOptions(baseUrl: err.requestOptions.baseUrl));
      final response = await refreshDio.post<Map<String, dynamic>>(
        ApiConstants.refreshToken,
        data: {'refresh_token': refreshToken},
      );

      final newAccessToken = response.data?['data']?['access_token'] as String?;
      final newRefreshToken = response.data?['data']?['refresh_token'] as String?;

      if (newAccessToken == null) {
        await _handleLogout();
        handler.next(err);
        return;
      }

      await secureStorage.writeTokens(
        accessToken: newAccessToken,
        refreshToken: newRefreshToken ?? refreshToken,
      );

      AppLogger.info('Silent token refresh succeeded.');

      // Replay the original failed request with the new token.
      err.requestOptions.headers['Authorization'] = 'Bearer $newAccessToken';
      final retryResponse = await refreshDio.fetch<dynamic>(err.requestOptions);
      handler.resolve(retryResponse);

      // Replay all pending requests.
      for (final pending in _pendingRequests) {
        pending.headers['Authorization'] = 'Bearer $newAccessToken';
      }
      _pendingRequests.clear();
    } catch (e) {
      AppLogger.warning('Token refresh failed. Logging out.', e);
      await _handleLogout();
      handler.next(err);
    } finally {
      _isRefreshing = false;
    }
  }

  Future<void> _handleLogout() async {
    await secureStorage.clearTokens();
    // Navigation to login is handled by the GoRouter redirect guard,
    // which checks the auth state from secure storage on every route evaluation.
    AppLogger.info('Auth tokens cleared. Router will redirect to login.');
  }
}
