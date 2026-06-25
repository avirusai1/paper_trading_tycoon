/// Paper Trading Tycoon — Validators Unit Tests
library;

import 'package:flutter_test/flutter_test.dart';
import 'package:paper_trading_tycoon/core/utils/validators.dart';

void main() {
  group('Validators.email', () {
    test('returns null for valid email', () {
      expect(Validators.email('user@example.com'), isNull);
    });

    test('returns error for empty input', () {
      expect(Validators.email(''), isNotNull);
      expect(Validators.email(null), isNotNull);
    });

    test('returns error for invalid format', () {
      expect(Validators.email('not-an-email'), isNotNull);
      expect(Validators.email('missing@tld'), isNotNull);
    });
  });

  group('Validators.password', () {
    test('accepts valid password', () {
      expect(Validators.password('Secure1234'), isNull);
    });

    test('rejects password shorter than 8 chars', () {
      expect(Validators.password('abc1'), isNotNull);
    });

    test('rejects password with no numbers', () {
      expect(Validators.password('onlyletters'), isNotNull);
    });

    test('rejects password with no letters', () {
      expect(Validators.password('12345678'), isNotNull);
    });
  });

  group('Validators.tradeQuantity', () {
    test('accepts valid quantity', () {
      expect(Validators.tradeQuantity('10', maxQuantity: 100), isNull);
    });

    test('rejects zero quantity', () {
      expect(Validators.tradeQuantity('0', maxQuantity: 100), isNotNull);
    });

    test('rejects quantity exceeding max', () {
      expect(Validators.tradeQuantity('101', maxQuantity: 100), isNotNull);
    });

    test('rejects non-numeric input', () {
      expect(Validators.tradeQuantity('abc', maxQuantity: 100), isNotNull);
    });
  });
}
