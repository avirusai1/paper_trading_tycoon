/// Paper Trading Tycoon — Display Formatters
///
/// Converts raw server values (paise integers, timestamps, percentages)
/// into human-readable formatted strings for the UI.
///
/// All monetary values from the API are in paise (integer).
/// Display formatting converts paise → INR with appropriate symbols.
/// See ADR-004 for the monetary representation decision.
library;

import 'package:intl/intl.dart';

/// Formatting utilities for currency, percentages, dates, and numbers.
abstract final class Formatters {
  static final NumberFormat _inrFormatter = NumberFormat.currency(
    locale: 'en_IN',
    symbol: '₹',
    decimalDigits: 2,
  );

  static final NumberFormat _inrCompactFormatter = NumberFormat.compactCurrency(
    locale: 'en_IN',
    symbol: '₹',
    decimalDigits: 1,
  );

  static final NumberFormat _percentFormatter = NumberFormat('+#,##0.00%;-#,##0.00%');
  static final NumberFormat _plainPercentFormatter = NumberFormat('#,##0.00%');

  // ── Currency (INR) ────────────────────────────────────────────────────────

  /// Formats a paise integer to a full INR display string.
  ///
  /// Example: 100000000 → '₹10,00,000.00'
  static String paise(int paise) {
    final rupees = paise / 100.0;
    return _inrFormatter.format(rupees);
  }

  /// Formats a paise integer to a compact INR string for space-constrained UI.
  ///
  /// Example: 100000000 → '₹10L'
  static String paiseCompact(int paise) {
    final rupees = paise / 100.0;
    return _inrCompactFormatter.format(rupees);
  }

  /// Formats a paise P&L value with a leading + or − sign.
  ///
  /// Example: 500000 → '+₹5,000.00', -200000 → '−₹2,000.00'
  static String paisePnl(int paise) {
    final rupees = paise / 100.0;
    final prefix = rupees >= 0 ? '+' : '';
    return '$prefix${_inrFormatter.format(rupees)}';
  }

  // ── Percentages ───────────────────────────────────────────────────────────

  /// Formats a decimal ratio as a signed percentage string.
  ///
  /// Example: 0.0542 → '+5.42%', -0.0125 → '-1.25%'
  static String signedPercent(double ratio) {
    return _percentFormatter.format(ratio);
  }

  /// Formats a decimal ratio as an unsigned percentage string.
  ///
  /// Example: 0.0542 → '5.42%'
  static String percent(double ratio) {
    return _plainPercentFormatter.format(ratio);
  }

  // ── Numbers ───────────────────────────────────────────────────────────────

  /// Formats a large integer with Indian number grouping (lakhs, crores).
  ///
  /// Example: 10000000 → '1,00,00,000'
  static String indianNumber(int value) {
    return NumberFormat('#,##,###', 'en_IN').format(value);
  }

  /// Formats a quantity as a compact human-readable string.
  ///
  /// Example: 1500000 → '15L', 1000 → '1K'
  static String compact(num value) {
    return NumberFormat.compact(locale: 'en_IN').format(value);
  }

  // ── Dates & Times ────────────────────────────────────────────────────────

  /// Formats a UTC [DateTime] to Indian date format.
  ///
  /// Example: DateTime(2025, 6, 15) → '15 Jun 2025'
  static String date(DateTime dateTime) {
    return DateFormat('d MMM yyyy').format(dateTime.toLocal());
  }

  /// Formats a [DateTime] to a short time string.
  ///
  /// Example: DateTime(...) → '03:45 PM'
  static String time(DateTime dateTime) {
    return DateFormat('hh:mm a').format(dateTime.toLocal());
  }

  /// Formats a [DateTime] to a full timestamp string.
  ///
  /// Example: DateTime(...) → '15 Jun 2025, 03:45 PM'
  static String dateTime(DateTime dt) {
    return DateFormat('d MMM yyyy, hh:mm a').format(dt.toLocal());
  }

  /// Returns a relative time string for notification / feed timestamps.
  ///
  /// Example: 2 minutes ago, 5 hours ago, Yesterday, 15 Jun 2025
  static String relativeTime(DateTime dateTime) {
    final now = DateTime.now();
    final diff = now.difference(dateTime.toLocal());

    if (diff.inSeconds < 60) return 'Just now';
    if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
    if (diff.inHours < 24) return '${diff.inHours}h ago';
    if (diff.inDays == 1) return 'Yesterday';
    if (diff.inDays < 7) return '${diff.inDays}d ago';
    return date(dateTime);
  }

  // ── Trade / Market ────────────────────────────────────────────────────────

  /// Formats a stock price in paise to a display string without currency symbol.
  ///
  /// Example: 45250 → '452.50'
  static String stockPrice(int paise) {
    final rupees = paise / 100.0;
    return NumberFormat('#,##,##0.00', 'en_IN').format(rupees);
  }
}
