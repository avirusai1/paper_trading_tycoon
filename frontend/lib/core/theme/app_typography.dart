/// Paper Trading Tycoon — Typography System
///
/// Defines the complete text style hierarchy for the application.
/// Built on Material Design 3 type scale with financial app adaptations:
/// - Tabular figures (lnum/tnum) for price and number columns.
/// - Generous line heights for readability in compact list items.
/// - Strong weight differentiation between headlines and body text.
library;

import 'package:flutter/material.dart';

abstract final class AppTypography {
  // ── Display — Large hero text (splash, level-up celebrations) ─────────────

  static const TextStyle displayLarge = TextStyle(
    fontSize: 57,
    fontWeight: FontWeight.w400,
    letterSpacing: -0.25,
    height: 1.12,
  );

  static const TextStyle displayMedium = TextStyle(
    fontSize: 45,
    fontWeight: FontWeight.w400,
    letterSpacing: 0,
    height: 1.16,
  );

  static const TextStyle displaySmall = TextStyle(
    fontSize: 36,
    fontWeight: FontWeight.w400,
    letterSpacing: 0,
    height: 1.22,
  );

  // ── Headline — Section headers, screen titles ──────────────────────────────

  static const TextStyle headlineLarge = TextStyle(
    fontSize: 32,
    fontWeight: FontWeight.w600,
    letterSpacing: 0,
    height: 1.25,
  );

  static const TextStyle headlineMedium = TextStyle(
    fontSize: 28,
    fontWeight: FontWeight.w600,
    letterSpacing: 0,
    height: 1.29,
  );

  static const TextStyle headlineSmall = TextStyle(
    fontSize: 24,
    fontWeight: FontWeight.w600,
    letterSpacing: 0,
    height: 1.33,
  );

  // ── Title — Card headers, dialog titles ──────────────────────────────────

  static const TextStyle titleLarge = TextStyle(
    fontSize: 22,
    fontWeight: FontWeight.w600,
    letterSpacing: 0,
    height: 1.27,
  );

  static const TextStyle titleMedium = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w600,
    letterSpacing: 0.15,
    height: 1.50,
  );

  static const TextStyle titleSmall = TextStyle(
    fontSize: 14,
    fontWeight: FontWeight.w600,
    letterSpacing: 0.1,
    height: 1.43,
  );

  // ── Body — General content, descriptions ─────────────────────────────────

  static const TextStyle bodyLarge = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w400,
    letterSpacing: 0.5,
    height: 1.50,
  );

  static const TextStyle bodyMedium = TextStyle(
    fontSize: 14,
    fontWeight: FontWeight.w400,
    letterSpacing: 0.25,
    height: 1.43,
  );

  static const TextStyle bodySmall = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w400,
    letterSpacing: 0.4,
    height: 1.33,
  );

  // ── Label — Chips, buttons, tabs, captions ────────────────────────────────

  static const TextStyle labelLarge = TextStyle(
    fontSize: 14,
    fontWeight: FontWeight.w500,
    letterSpacing: 0.1,
    height: 1.43,
  );

  static const TextStyle labelMedium = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w500,
    letterSpacing: 0.5,
    height: 1.33,
  );

  static const TextStyle labelSmall = TextStyle(
    fontSize: 11,
    fontWeight: FontWeight.w500,
    letterSpacing: 0.5,
    height: 1.45,
  );

  // ── Financial — Price display with tabular figures ─────────────────────────

  /// Large price display (e.g. portfolio total value).
  static const TextStyle priceHero = TextStyle(
    fontSize: 36,
    fontWeight: FontWeight.w700,
    letterSpacing: -0.5,
    height: 1.1,
    fontFeatures: [FontFeature.tabularFigures()],
  );

  /// Standard stock price in list items.
  static const TextStyle priceMedium = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w600,
    letterSpacing: 0,
    height: 1.25,
    fontFeatures: [FontFeature.tabularFigures()],
  );

  /// Compact price in dense lists.
  static const TextStyle priceSmall = TextStyle(
    fontSize: 13,
    fontWeight: FontWeight.w500,
    letterSpacing: 0,
    height: 1.2,
    fontFeatures: [FontFeature.tabularFigures()],
  );

  /// P&L percentage change.
  static const TextStyle changePercent = TextStyle(
    fontSize: 13,
    fontWeight: FontWeight.w600,
    letterSpacing: 0,
    height: 1.2,
    fontFeatures: [FontFeature.tabularFigures()],
  );

  // ── Builds a complete [TextTheme] from the style definitions above ─────────

  static TextTheme get textTheme => const TextTheme(
    displayLarge: displayLarge,
    displayMedium: displayMedium,
    displaySmall: displaySmall,
    headlineLarge: headlineLarge,
    headlineMedium: headlineMedium,
    headlineSmall: headlineSmall,
    titleLarge: titleLarge,
    titleMedium: titleMedium,
    titleSmall: titleSmall,
    bodyLarge: bodyLarge,
    bodyMedium: bodyMedium,
    bodySmall: bodySmall,
    labelLarge: labelLarge,
    labelMedium: labelMedium,
    labelSmall: labelSmall,
  );
}
