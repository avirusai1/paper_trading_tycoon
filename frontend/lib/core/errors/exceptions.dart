/// Paper Trading Tycoon — Data Layer Exceptions
///
/// Typed exceptions thrown exclusively from the data layer (repositories,
/// remote data sources, local data sources). The domain layer never catches
/// these directly — repositories translate them into [Failure] types so that
/// presentation logic remains decoupled from data-source concerns.
library;

/// Base exception for all application-level data errors.
sealed class AppException implements Exception {
  const AppException({
    required this.message,
    this.statusCode,
    this.data,
  });

  /// Human-readable description of the error (for logging, not display).
  final String message;

  /// HTTP status code if the exception originated from an API response.
  final int? statusCode;

  /// Optional raw response data for debugging.
  final dynamic data;

  @override
  String toString() => 'AppException(message: $message, statusCode: $statusCode)';
}

/// Thrown when the device has no internet connectivity.
final class NetworkException extends AppException {
  const NetworkException({super.message = 'No internet connection available.'});
}

/// Thrown when a network request times out.
final class TimeoutException extends AppException {
  const TimeoutException({super.message = 'The request timed out. Please try again.'});
}

/// Thrown for any non-successful HTTP response from the Laravel API.
final class ServerException extends AppException {
  const ServerException({
    required super.message,
    required int statusCode,
    super.data,
  }) : super(statusCode: statusCode);
}

/// Thrown when the API returns HTTP 401 — token expired or invalid.
final class UnauthorizedException extends AppException {
  const UnauthorizedException({super.message = 'Your session has expired. Please log in again.'});
}

/// Thrown when the API returns HTTP 403 — authenticated but not permitted.
final class ForbiddenException extends AppException {
  const ForbiddenException({super.message = 'You do not have permission to perform this action.'});
}

/// Thrown when the API returns HTTP 404.
final class NotFoundException extends AppException {
  const NotFoundException({super.message = 'The requested resource was not found.'});
}

/// Thrown when the API returns HTTP 422 — validation failure.
/// Contains field-level validation errors for form feedback.
final class ValidationException extends AppException {
  const ValidationException({
    required super.message,
    required this.errors,
  }) : super(statusCode: 422);

  /// Map of field name → list of error messages (mirrors Laravel's 422 format).
  final Map<String, List<String>> errors;
}

/// Thrown when the API returns HTTP 429 — rate limit exceeded.
final class RateLimitException extends AppException {
  const RateLimitException({
    super.message = 'Too many requests. Please slow down.',
    this.retryAfterSeconds,
  });

  /// Seconds to wait before retrying, from the Retry-After header.
  final int? retryAfterSeconds;
}

/// Thrown when reading from or writing to local storage (Hive) fails.
final class LocalStorageException extends AppException {
  const LocalStorageException({required super.message});
}

/// Thrown when a JSON deserialization operation fails.
final class ParseException extends AppException {
  const ParseException({
    required super.message,
    this.rawData,
  });

  /// The raw string that failed to parse (for debug logging only).
  final String? rawData;
}

/// Thrown when an operation requires authentication but no valid token exists.
final class NotAuthenticatedException extends AppException {
  const NotAuthenticatedException({super.message = 'Authentication required.'});
}
