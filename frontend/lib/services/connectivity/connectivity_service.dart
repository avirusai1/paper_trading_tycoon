/// Paper Trading Tycoon — Connectivity Service
///
/// Monitors device internet connectivity using the `connectivity_plus` package.
/// Exposed as a Riverpod StreamProvider so UI components can reactively
/// respond to network changes (e.g. show an offline banner).
///
/// Trading actions on the Flutter side also check connectivity before
/// dispatching to reduce latency of failure feedback.
library;

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Stream of connectivity results — emits on every network change.
final connectivityStreamProvider = StreamProvider<List<ConnectivityResult>>((ref) {
  return Connectivity().onConnectivityChanged;
});

/// Synchronous check of the current connectivity status.
final isOnlineProvider = FutureProvider<bool>((ref) async {
  final result = await Connectivity().checkConnectivity();
  return _isConnected(result);
});

/// Reactive provider that tracks whether the device is currently online.
final isOnlineStreamProvider = StreamProvider<bool>((ref) {
  return Connectivity().onConnectivityChanged.map(_isConnected);
});

bool _isConnected(List<ConnectivityResult> results) {
  return results.any(
    (r) =>
        r == ConnectivityResult.mobile ||
        r == ConnectivityResult.wifi ||
        r == ConnectivityResult.ethernet,
  );
}

/// Service class for imperative connectivity checks in non-widget code.
final connectivityServiceProvider = Provider<ConnectivityService>((ref) {
  return ConnectivityService();
});

final class ConnectivityService {
  /// Returns true if the device has an active internet connection.
  Future<bool> isOnline() async {
    final results = await Connectivity().checkConnectivity();
    return _isConnected(results);
  }
}
