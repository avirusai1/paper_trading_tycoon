/// Paper Trading Tycoon — Formatters Unit Tests
library;

import 'package:flutter_test/flutter_test.dart';
import 'package:paper_trading_tycoon/core/utils/formatters.dart';

void main() {
  group('Formatters.paise', () {
    test('converts 100000000 paise to ₹10,00,000.00', () {
      expect(Formatters.paise(100000000), contains('10,00,000'));
    });

    test('converts 0 paise to ₹0.00', () {
      expect(Formatters.paise(0), contains('0.00'));
    });
  });

  group('Formatters.signedPercent', () {
    test('formats positive ratio with + prefix', () {
      final result = Formatters.signedPercent(0.0542);
      expect(result, contains('+'));
      expect(result, contains('%'));
    });

    test('formats negative ratio with - prefix', () {
      final result = Formatters.signedPercent(-0.0125);
      expect(result, contains('-'));
    });
  });

  group('Formatters.relativeTime', () {
    test('returns Just now for recent timestamps', () {
      final now = DateTime.now().subtract(const Duration(seconds: 10));
      expect(Formatters.relativeTime(now), 'Just now');
    });

    test('returns minutes ago for timestamps under 1 hour', () {
      final twoMinsAgo = DateTime.now().subtract(const Duration(minutes: 2));
      expect(Formatters.relativeTime(twoMinsAgo), '2m ago');
    });
  });
}
