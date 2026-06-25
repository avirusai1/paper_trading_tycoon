/// Paper Trading Tycoon — Numeric Extensions
///
/// Convenience extensions on [num], [int], and [double] for
/// monetary conversions, formatting shortcuts, and game-economy
/// calculations used throughout the UI.
library;

extension IntExtensions on int {
  // ── Monetary Conversions ─────────────────────────────────────────────────

  /// Converts this paise integer to a rupees [double].
  ///
  /// Example: 100000000.toRupees → 1000000.0
  double get toRupees => this / 100.0;

  /// Whether this paise value represents a positive amount.
  bool get isPositivePaise => this > 0;

  /// Whether this paise value represents a negative amount.
  bool get isNegativePaise => this < 0;

  // ── Number Utilities ─────────────────────────────────────────────────────

  /// Clamps this integer between [min] and [max].
  int clampBetween(int min, int max) => clamp(min, max).toInt();

  /// Whether this integer is between [min] and [max] (inclusive).
  bool isBetween(int min, int max) => this >= min && this <= max;
}

extension DoubleExtensions on double {
  // ── Monetary Conversions ─────────────────────────────────────────────────

  /// Converts this rupees [double] to paise [int] (rounded).
  ///
  /// Example: 1000000.0.toPaise → 100000000
  int get toPaise => (this * 100).round();

  // ── Percentage Helpers ───────────────────────────────────────────────────

  /// Converts a percentage value (e.g. 5.42) to a ratio (0.0542).
  double get percentToRatio => this / 100.0;

  /// Converts a ratio (0.0542) to a percentage value (5.42).
  double get ratioToPercent => this * 100.0;

  /// Whether this ratio represents a gain (positive P&L).
  bool get isGain => this >= 0;

  /// Whether this ratio represents a loss (negative P&L).
  bool get isLoss => this < 0;
}

extension NumExtensions on num {
  /// Returns true if this number is between [min] and [max] (inclusive).
  bool isBetween(num min, num max) => this >= min && this <= max;
}
