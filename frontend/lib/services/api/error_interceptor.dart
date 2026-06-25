/// Paper Trading Tycoon — Error Interceptor
///
/// Translates Dio-level errors and HTTP error responses into typed
/// [AppException] instances for consistent handling throughout the app.
///
/// This interceptor runs last in the chain, after retry has given up.
/// All exceptions thrown here are caught by the repository layer and
/// converted to [Failure] types via [ErrorMapper].
library;

import 'package:dio/dio.dart';

import '../../core/errors/exceptions.dart';

/// Dio interceptor that converts error responses to typed [AppException] types.
final class ErrorInterceptor extends Interceptor {
  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    final exception = _mapToException(err);
    // Reject with a modified DioException carrying our typed exception.
    handler.reject(
      DioException(
        requestOptions: err.requestOptions,
        response: err.response,
        type: err.type,
        error: exception,
      ),
    );
  }

  AppException _mapToException(DioException err) {
    return switch (err.type) {
      DioExceptionType.connectionError => const NetworkException(),
      DioExceptionType.connectionTimeout => const TimeoutException(),
      DioExceptionType.receiveTimeout => const TimeoutException(),
      DioExceptionType.sendTimeout => const TimeoutException(),
      DioExceptionType.badResponse => _mapStatusCode(err),
      DioExceptionType.cancel => const NetworkException(message: 'Request was cancelled.'),
      _ => ServerException(
          message: err.message ?? 'An unexpected network error occurred.',
          statusCode: err.response?.statusCode ?? 0,
        ),
    };
  }

  AppException _mapStatusCode(DioException err) {
    final statusCode = err.response?.statusCode ?? 0;
    final data = err.response?.data;

    final message = _extractMessage(data) ?? _defaultMessageForStatus(statusCode);

    return switch (statusCode) {
      401 => UnauthorizedException(message: message),
      403 => ForbiddenException(message: message),
      404 => NotFoundException(message: message),
      422 => ValidationException(
          message: message,
          errors: _extractFieldErrors(data),
        ),
      429 => RateLimitException(
          message: message,
          retryAfterSeconds: _extractRetryAfter(err.response),
        ),
      _ => ServerException(message: message, statusCode: statusCode),
    };
  }

  String _defaultMessageForStatus(int statusCode) {
    return switch (statusCode) {
      400 => 'Invalid request.',
      401 => 'Authentication required.',
      403 => 'Access denied.',
      404 => 'Resource not found.',
      422 => 'Validation failed.',
      429 => 'Too many requests.',
      500 => 'Server error. Please try again later.',
      503 => 'Service temporarily unavailable.',
      _ => 'An error occurred (HTTP $statusCode).',
    };
  }

  String? _extractMessage(dynamic data) {
    if (data is Map<String, dynamic>) {
      return data['message'] as String?;
    }
    return null;
  }

  Map<String, List<String>> _extractFieldErrors(dynamic data) {
    if (data is Map<String, dynamic> && data['errors'] is Map) {
      final raw = data['errors'] as Map<String, dynamic>;
      return raw.map(
        (key, value) => MapEntry(
          key,
          (value as List<dynamic>).cast<String>(),
        ),
      );
    }
    return {};
  }

  int? _extractRetryAfter(Response<dynamic>? response) {
    final header = response?.headers.value('Retry-After');
    if (header != null) return int.tryParse(header);
    return null;
  }
}
