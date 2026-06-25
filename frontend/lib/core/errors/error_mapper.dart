/// Paper Trading Tycoon — Error Mapper
///
/// Translates [AppException] types (thrown in the data layer) into
/// [Failure] types (used in the domain/presentation layers).
/// All repository catch blocks delegate to this mapper.
library;

import 'exceptions.dart';
import 'failures.dart';

/// Maps a caught [AppException] to the appropriate [Failure] subtype.
///
/// Usage inside a repository:
/// ```dart
/// try {
///   return Right(await remoteDataSource.fetchProfile());
/// } on AppException catch (e) {
///   return Left(ErrorMapper.fromException(e));
/// }
/// ```
abstract final class ErrorMapper {
  /// Converts an [AppException] to a [Failure].
  static Failure fromException(AppException exception) {
    return switch (exception) {
      NetworkException() => const NetworkFailure(),
      TimeoutException() => const TimeoutFailure(),
      UnauthorizedException() => const UnauthorizedFailure(),
      ForbiddenException() => const ForbiddenFailure(),
      NotFoundException() => const NotFoundFailure(),
      ValidationException(:final message, :final errors) =>
        ValidationFailure(message: message, fieldErrors: errors),
      RateLimitException(:final retryAfterSeconds) =>
        RateLimitFailure(retryAfterSeconds: retryAfterSeconds),
      LocalStorageException(:final message) => StorageFailure(message: message),
      ParseException() => const ParseFailure(),
      NotAuthenticatedException() => const UnauthorizedFailure(),
      ServerException(:final message, :final statusCode) =>
        ServerFailure(message: message, statusCode: statusCode),
    };
  }

  /// Converts any generic [Object] to a [Failure].
  /// Handles the case where a non-[AppException] is caught.
  static Failure fromError(Object error) {
    if (error is AppException) return fromException(error);
    return const UnexpectedFailure();
  }
}
