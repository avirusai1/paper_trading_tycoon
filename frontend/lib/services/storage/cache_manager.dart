/// Paper Trading Tycoon — Cache Manager
///
/// A generic TTL-based cache built on Hive for storing read-only server data.
/// Entries are stored with an expiry timestamp; stale entries are treated as
/// cache misses and the caller is expected to refetch.
///
/// Used for: stock lists, feature flags, portfolio snapshots.
/// NOT used for: auth tokens (use SecureStorageService) or user preferences.
library;

import 'dart:convert';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:hive_flutter/hive_flutter.dart';

import '../../core/utils/logger.dart';

/// Provides the [CacheManager] singleton.
final cacheManagerProvider = Provider<CacheManager>((ref) => CacheManager());

/// Generic Hive-backed cache with TTL support.
final class CacheManager {
  /// Writes a JSON-serializable [value] to [boxName] under [key].
  /// The entry expires after [ttl].
  Future<void> put<T>({
    required String boxName,
    required String key,
    required T value,
    required Duration ttl,
  }) async {
    final box = Hive.box<dynamic>(boxName);
    final entry = {
      'value': jsonEncode(value),
      'expires_at': DateTime.now().add(ttl).millisecondsSinceEpoch,
    };
    await box.put(key, jsonEncode(entry));
    AppLogger.debug('Cache put: $boxName/$key (TTL: ${ttl.inSeconds}s)');
  }

  /// Reads a cached value from [boxName] under [key].
  /// Returns null if the entry is missing or expired.
  dynamic get({required String boxName, required String key}) {
    final box = Hive.box<dynamic>(boxName);
    final raw = box.get(key) as String?;
    if (raw == null) return null;

    try {
      final entry = jsonDecode(raw) as Map<String, dynamic>;
      final expiresAt = entry['expires_at'] as int;

      if (DateTime.now().millisecondsSinceEpoch > expiresAt) {
        AppLogger.debug('Cache miss (expired): $boxName/$key');
        return null;
      }

      return jsonDecode(entry['value'] as String);
    } catch (e) {
      AppLogger.warning('Cache read error for $boxName/$key', e);
      return null;
    }
  }

  /// Checks whether a non-expired entry exists for [key] in [boxName].
  bool has({required String boxName, required String key}) {
    return get(boxName: boxName, key: key) != null;
  }

  /// Removes a specific entry.
  Future<void> invalidate({required String boxName, required String key}) async {
    await Hive.box<dynamic>(boxName).delete(key);
    AppLogger.debug('Cache invalidated: $boxName/$key');
  }

  /// Clears all entries in a given box.
  Future<void> clearBox(String boxName) async {
    await Hive.box<dynamic>(boxName).clear();
    AppLogger.debug('Cache box cleared: $boxName');
  }
}
