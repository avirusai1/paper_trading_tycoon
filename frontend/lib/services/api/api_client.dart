/// Paper Trading Tycoon — API Client
///
/// Thin wrapper around the configured [Dio] instance that provides
/// typed request methods with consistent response handling.
///
/// All methods return the unwrapped `data` field from the Laravel
/// API envelope `{ "success": true, "data": {...} }`.
///
/// Throws [AppException] subtypes on failure (via [ErrorInterceptor]).
library;

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/errors/exceptions.dart';
import 'dio_client.dart';

/// Provides the [ApiClient] instance.
final apiClientProvider = Provider<ApiClient>((ref) {
  return ApiClient(dio: ref.watch(dioProvider));
});

/// HTTP client for all Laravel API interactions.
final class ApiClient {
  ApiClient({required this.dio});

  final Dio dio;

  // ── GET ───────────────────────────────────────────────────────────────────

  /// Performs a GET request and returns the `data` field of the response.
  Future<dynamic> get(
    String path, {
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    final response = await dio.get<Map<String, dynamic>>(
      path,
      queryParameters: queryParameters,
      options: options,
    );
    return _unwrap(response);
  }

  // ── POST ──────────────────────────────────────────────────────────────────

  /// Performs a POST request and returns the `data` field of the response.
  Future<dynamic> post(
    String path, {
    dynamic body,
    Map<String, dynamic>? queryParameters,
    String? idempotencyKey,
    Options? options,
  }) async {
    final mergedOptions = (options ?? Options()).copyWith(
      headers: {
        ...?options?.headers,
        if (idempotencyKey != null) 'Idempotency-Key': idempotencyKey,
      },
    );
    final response = await dio.post<Map<String, dynamic>>(
      path,
      data: body,
      queryParameters: queryParameters,
      options: mergedOptions,
    );
    return _unwrap(response);
  }

  // ── PUT ───────────────────────────────────────────────────────────────────

  Future<dynamic> put(
    String path, {
    dynamic body,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    final response = await dio.put<Map<String, dynamic>>(
      path,
      data: body,
      queryParameters: queryParameters,
      options: options,
    );
    return _unwrap(response);
  }

  // ── PATCH ─────────────────────────────────────────────────────────────────

  Future<dynamic> patch(
    String path, {
    dynamic body,
    Options? options,
  }) async {
    final response = await dio.patch<Map<String, dynamic>>(
      path,
      data: body,
      options: options,
    );
    return _unwrap(response);
  }

  // ── DELETE ────────────────────────────────────────────────────────────────

  Future<void> delete(String path, {Options? options}) async {
    await dio.delete<dynamic>(path, options: options);
  }

  // ── Response unwrapping ───────────────────────────────────────────────────

  /// Extracts the `data` field from the Laravel API envelope.
  /// Throws [ParseException] if the envelope format is unexpected.
  dynamic _unwrap(Response<Map<String, dynamic>> response) {
    final body = response.data;
    if (body == null) return null;

    final success = body['success'] as bool?;
    if (success == false) {
      throw ServerException(
        message: body['message'] as String? ?? 'Request failed.',
        statusCode: response.statusCode ?? 0,
        data: body,
      );
    }

    return body['data'];
  }
}
