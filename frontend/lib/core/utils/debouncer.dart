/// Paper Trading Tycoon — Debouncer Utility
///
/// Prevents excessive function calls during rapid UI events such as
/// search input, scroll events, or button taps. Cancels any pending
/// execution and restarts the timer on each call.
library;

import 'dart:async';

/// Debounces a callback so it only fires after [duration] has elapsed
/// since the last call.
///
/// Example (stock search):
/// ```dart
/// final _debouncer = Debouncer(delay: Duration(milliseconds: 400));
///
/// void onSearchChanged(String query) {
///   _debouncer.run(() => ref.read(stockSearchProvider.notifier).search(query));
/// }
/// ```
final class Debouncer {
  Debouncer({required this.delay});

  /// Duration to wait after the last call before executing.
  final Duration delay;

  Timer? _timer;

  /// Schedules [action] to run after [delay].
  /// Cancels any previously scheduled call.
  void run(void Function() action) {
    _timer?.cancel();
    _timer = Timer(delay, action);
  }

  /// Cancels any pending debounced call.
  void cancel() {
    _timer?.cancel();
    _timer = null;
  }

  /// Whether a debounced call is currently pending.
  bool get isPending => _timer?.isActive ?? false;

  /// Disposes the debouncer — must be called when the owning widget is disposed.
  void dispose() {
    _timer?.cancel();
    _timer = null;
  }
}
