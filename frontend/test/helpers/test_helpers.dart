/// Paper Trading Tycoon — Test Helpers
///
/// Common utilities for all Flutter test types.
/// Provides mock providers, fake data builders, and assertion helpers.
library;

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Wraps a widget in a ProviderScope for widget testing.
/// [overrides] allows injecting mock providers.
Widget buildTestWidget(
  Widget child, {
  List<Override> overrides = const [],
}) {
  return ProviderScope(
    overrides: overrides,
    child: MaterialApp(
      home: child,
    ),
  );
}

/// Builds a ProviderContainer with optional overrides for unit testing.
ProviderContainer buildTestContainer({
  List<Override> overrides = const [],
}) {
  return ProviderContainer(overrides: overrides);
}
