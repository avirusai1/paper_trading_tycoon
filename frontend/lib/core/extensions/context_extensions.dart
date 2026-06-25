/// Paper Trading Tycoon — BuildContext Extensions
///
/// Convenience extensions on [BuildContext] for accessing theme data,
/// screen dimensions, and navigation without verbose boilerplate.
library;

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

extension ContextExtensions on BuildContext {
  // ── Theme ─────────────────────────────────────────────────────────────────

  /// The current [ThemeData].
  ThemeData get theme => Theme.of(this);

  /// The current [ColorScheme].
  ColorScheme get colorScheme => Theme.of(this).colorScheme;

  /// The current [TextTheme].
  TextTheme get textTheme => Theme.of(this).textTheme;

  /// Whether the current theme is dark mode.
  bool get isDarkMode => Theme.of(this).brightness == Brightness.dark;

  // ── Dimensions ───────────────────────────────────────────────────────────

  /// The [MediaQueryData] for this context.
  MediaQueryData get mediaQuery => MediaQuery.of(this);

  /// Screen width in logical pixels.
  double get screenWidth => MediaQuery.of(this).size.width;

  /// Screen height in logical pixels.
  double get screenHeight => MediaQuery.of(this).size.height;

  /// Safe area top padding (status bar + notch).
  double get safeAreaTop => MediaQuery.of(this).padding.top;

  /// Safe area bottom padding (home indicator).
  double get safeAreaBottom => MediaQuery.of(this).padding.bottom;

  /// Whether the keyboard is currently visible.
  bool get isKeyboardVisible => MediaQuery.of(this).viewInsets.bottom > 0;

  // ── Navigation ────────────────────────────────────────────────────────────

  /// Pushes a named route using GoRouter.
  void pushNamed(String name, {Map<String, String> pathParameters = const {}}) {
    GoRouter.of(this).pushNamed(name, pathParameters: pathParameters);
  }

  /// Replaces the current route using GoRouter.
  void replaceNamed(String name, {Map<String, String> pathParameters = const {}}) {
    GoRouter.of(this).pushReplacementNamed(name, pathParameters: pathParameters);
  }

  /// Pops the current route if the navigator can pop.
  void popIfPossible() {
    if (canPop()) pop();
  }

  // ── Snackbar convenience ──────────────────────────────────────────────────

  /// Shows a brief informational snackbar.
  void showSnackBar(String message) {
    ScaffoldMessenger.of(this)
      ..clearSnackBars()
      ..showSnackBar(SnackBar(content: Text(message)));
  }

  /// Shows an error snackbar with red background.
  void showErrorSnackBar(String message) {
    ScaffoldMessenger.of(this)
      ..clearSnackBars()
      ..showSnackBar(
        SnackBar(
          content: Text(message),
          backgroundColor: colorScheme.error,
        ),
      );
  }
}
