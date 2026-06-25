/// Paper Trading Tycoon — Domain Layer Failures
///
/// Failures are the domain layer's representation of errors. Repositories
/// catch [AppException] types and return [Either<Failure, T>] so that use
/// cases and presentation logic deal only with domain-level failure types,
/// not data-source specifics.
///
/// Uses the `dartz` package's [Either] type for functional error handling.
library;

import 'package:equatable/equatable.dart';

/// Base failure type. All domain failures extend this.
sealed class Failure extends Equatable {
  const Failure({required this.message});

  /// User-facing error description. Must be suitable for display in the UI.
  final String message;

  @override
  List<Object?> get props => [message];
}

/// The device has no internet connection.
final class NetworkFailure extends Failure {
  const NetworkFailure({super.message = 'No internet connection. Check your network and try again.'});
}

/// A network request timed out.
final class TimeoutFailure extends Failure {
  const TimeoutFailure({super.message = 'Request timed out. Please try again.'});
}

/// An unexpected server-side error (HTTP 500).
final class ServerFailure extends Failure {
  const ServerFailure({required super.message, this.statusCode});

  final int? statusCode;

  @override
  List<Object?> get props => [message, statusCode];
}

/// The user's session has expired or the token is invalid (HTTP 401).
final class UnauthorizedFailure extends Failure {
  const UnauthorizedFailure({super.message = 'Your session has expired. Please log in again.'});
}

/// The user is authenticated but lacks permission for this action (HTTP 403).
final class ForbiddenFailure extends Failure {
  const ForbiddenFailure({super.message = 'You don\'t have permission to do this.'});
}

/// A requested resource does not exist (HTTP 404).
final class NotFoundFailure extends Failure {
  const NotFoundFailure({super.message = 'The requested resource was not found.'});
}

/// Input validation failed (HTTP 422).
/// Includes field-level errors for form-specific error display.
final class ValidationFailure extends Failure {
  const ValidationFailure({
    required super.message,
    required this.fieldErrors,
  });

  /// Map of field name → error messages.
  final Map<String, List<String>> fieldErrors;

  @override
  List<Object?> get props => [message, fieldErrors];
}

/// The user has exceeded rate limits (HTTP 429).
final class RateLimitFailure extends Failure {
  const RateLimitFailure({
    super.message = 'Too many requests. Please wait before trying again.',
    this.retryAfterSeconds,
  });

  final int? retryAfterSeconds;

  @override
  List<Object?> get props => [message, retryAfterSeconds];
}

/// Local storage (Hive) read/write failure.
final class StorageFailure extends Failure {
  const StorageFailure({required super.message});
}

/// JSON parsing or deserialization failure.
final class ParseFailure extends Failure {
  const ParseFailure({super.message = 'Failed to process server response.'});
}

/// A trading-specific business rule was violated.
/// e.g. insufficient funds, market closed, invalid quantity.
final class TradeFailure extends Failure {
  const TradeFailure({required super.message, required this.code});

  /// Machine-readable error code from the API (e.g. 'insufficient_funds').
  final String code;

  @override
  List<Object?> get props => [message, code];
}

/// An unexpected error that doesn't fit a known category.
final class UnexpectedFailure extends Failure {
  const UnexpectedFailure({super.message = 'An unexpected error occurred. Please try again.'});
}
