/// Paper Trading Tycoon — Secure Storage Service
///
/// Wraps [FlutterSecureStorage] for all sensitive authentication data:
///   - JWT access token
///   - JWT refresh token
///   - Token expiry timestamp
///   - Authenticated user ID
///
/// Uses AES encryption on Android (KeyStore) and Keychain on iOS.
/// Never store non-sensitive preferences here — use Hive instead.
library;

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../../core/constants/storage_keys.dart';
import '../../core/utils/logger.dart';

/// Provides the [SecureStorageService] singleton.
final secureStorageServiceProvider = Provider<SecureStorageService>((ref) {
  return SecureStorageService();
});

/// Service for reading and writing sensitive authentication credentials.
final class SecureStorageService {
  final FlutterSecureStorage _storage = const FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
    iOptions: IOSOptions(
      accessibility: KeychainAccessibility.first_unlock_this_device,
    ),
  );

  // ── Token Management ──────────────────────────────────────────────────────

  /// Persists the access and refresh tokens after successful login/refresh.
  Future<void> writeTokens({
    required String accessToken,
    required String refreshToken,
    DateTime? expiresAt,
  }) async {
    await Future.wait([
      _storage.write(key: SecureStorageKeys.accessToken, value: accessToken),
      _storage.write(key: SecureStorageKeys.refreshToken, value: refreshToken),
      if (expiresAt != null)
        _storage.write(
          key: SecureStorageKeys.tokenExpiresAt,
          value: expiresAt.toIso8601String(),
        ),
    ]);
    AppLogger.debug('Auth tokens written to secure storage.');
  }

  /// Reads the current access token.
  Future<String?> readAccessToken() async {
    return _storage.read(key: SecureStorageKeys.accessToken);
  }

  /// Reads the current refresh token.
  Future<String?> readRefreshToken() async {
    return _storage.read(key: SecureStorageKeys.refreshToken);
  }

  /// Reads the token expiry timestamp.
  Future<DateTime?> readTokenExpiry() async {
    final value = await _storage.read(key: SecureStorageKeys.tokenExpiresAt);
    if (value == null) return null;
    return DateTime.tryParse(value);
  }

  /// Whether a valid (non-expired) access token exists.
  Future<bool> hasValidToken() async {
    final token = await readAccessToken();
    if (token == null) return false;

    final expiry = await readTokenExpiry();
    if (expiry == null) return true; // No expiry stored — assume valid.

    // Treat token as expired 30 seconds before actual expiry (clock skew buffer).
    return expiry.isAfter(DateTime.now().add(const Duration(seconds: 30)));
  }

  // ── User Identity ─────────────────────────────────────────────────────────

  /// Persists the authenticated user's ID.
  Future<void> writeUserId(String userId) async {
    await _storage.write(key: SecureStorageKeys.userId, value: userId);
  }

  /// Reads the stored user ID.
  Future<String?> readUserId() async {
    return _storage.read(key: SecureStorageKeys.userId);
  }

  // ── Cleanup ───────────────────────────────────────────────────────────────

  /// Clears all auth tokens on logout.
  Future<void> clearTokens() async {
    await Future.wait([
      _storage.delete(key: SecureStorageKeys.accessToken),
      _storage.delete(key: SecureStorageKeys.refreshToken),
      _storage.delete(key: SecureStorageKeys.tokenExpiresAt),
      _storage.delete(key: SecureStorageKeys.userId),
    ]);
    AppLogger.info('Auth tokens cleared from secure storage.');
  }
}
