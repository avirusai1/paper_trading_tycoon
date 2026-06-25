/// Paper Trading Tycoon — DateTime Extensions
///
/// Convenience extensions on [DateTime] for market-context operations,
/// date comparisons, and display formatting relevant to Indian trading hours.
library;

extension DateTimeExtensions on DateTime {
  // ── Market Hours (NSE/BSE) ────────────────────────────────────────────────

  /// Whether the NSE/BSE market is open at this [DateTime] (IST).
  ///
  /// NSE regular hours: Monday–Friday, 09:15 – 15:30 IST.
  /// This is a client-side approximation; the authoritative check is
  /// the server's Rules Engine market-status endpoint.
  bool get isMarketHours {
    final ist = toIst();
    if (ist.weekday == DateTime.saturday || ist.weekday == DateTime.sunday) {
      return false;
    }
    final openTime = DateTime(ist.year, ist.month, ist.day, 9, 15);
    final closeTime = DateTime(ist.year, ist.month, ist.day, 15, 30);
    return ist.isAfter(openTime) && ist.isBefore(closeTime);
  }

  /// Converts this [DateTime] (assumed UTC or local) to Indian Standard Time (UTC+5:30).
  DateTime toIst() {
    return toUtc().add(const Duration(hours: 5, minutes: 30));
  }

  // ── Comparisons ───────────────────────────────────────────────────────────

  /// Whether this [DateTime] is the same calendar day as [other].
  bool isSameDay(DateTime other) {
    return year == other.year && month == other.month && day == other.day;
  }

  /// Whether this [DateTime] is today (local time).
  bool get isToday => isSameDay(DateTime.now());

  /// Whether this [DateTime] was yesterday (local time).
  bool get isYesterday {
    final yesterday = DateTime.now().subtract(const Duration(days: 1));
    return isSameDay(yesterday);
  }

  // ── Formatting ────────────────────────────────────────────────────────────

  /// Returns a short date string: '15 Jun 2025'.
  String get shortDate {
    const months = [
      'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
      'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
    ];
    return '$day ${months[month - 1]} $year';
  }

  /// Returns a compact date key for grouping: '2025-06-15'.
  String get dateKey => '${year.toString().padLeft(4, '0')}-'
      '${month.toString().padLeft(2, '0')}-'
      '${day.toString().padLeft(2, '0')}';

  /// Returns the start of this day at midnight (00:00:00).
  DateTime get startOfDay => DateTime(year, month, day);

  /// Returns the end of this day at 23:59:59.999.
  DateTime get endOfDay => DateTime(year, month, day, 23, 59, 59, 999);
}
