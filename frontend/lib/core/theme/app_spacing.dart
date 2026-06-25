/// Paper Trading Tycoon — Spacing & Sizing System
///
/// 4px base grid spacing system. All padding, margin, and gap values
/// are multiples of 4. Using named constants enforces visual consistency
/// and makes global spacing adjustments trivial.
library;

import 'package:flutter/material.dart';

/// Spacing constants on a 4px base grid.
abstract final class AppSpacing {
  static const double xs = 4;
  static const double sm = 8;
  static const double md = 12;
  static const double base = 16;
  static const double lg = 20;
  static const double xl = 24;
  static const double xxl = 32;
  static const double xxxl = 40;
  static const double huge = 48;
  static const double massive = 64;

  // ── Edge Insets shortcuts ─────────────────────────────────────────────────

  static const EdgeInsets paddingXS = EdgeInsets.all(xs);
  static const EdgeInsets paddingSM = EdgeInsets.all(sm);
  static const EdgeInsets paddingMD = EdgeInsets.all(md);
  static const EdgeInsets paddingBase = EdgeInsets.all(base);
  static const EdgeInsets paddingLG = EdgeInsets.all(lg);
  static const EdgeInsets paddingXL = EdgeInsets.all(xl);

  /// Standard horizontal screen padding.
  static const EdgeInsets screenPadding = EdgeInsets.symmetric(horizontal: base);

  /// Padding for list tile content.
  static const EdgeInsets listTilePadding = EdgeInsets.symmetric(
    horizontal: base,
    vertical: sm,
  );

  /// Padding for card content areas.
  static const EdgeInsets cardPadding = EdgeInsets.all(base);

  /// Padding for bottom sheets.
  static const EdgeInsets sheetPadding = EdgeInsets.fromLTRB(base, xl, base, xxl);

  // ── SizedBox gaps ─────────────────────────────────────────────────────────

  static const Widget gapXS = SizedBox(height: xs, width: xs);
  static const Widget gapSM = SizedBox(height: sm, width: sm);
  static const Widget gapMD = SizedBox(height: md, width: md);
  static const Widget gapBase = SizedBox(height: base, width: base);
  static const Widget gapLG = SizedBox(height: lg, width: lg);
  static const Widget gapXL = SizedBox(height: xl, width: xl);
  static const Widget gapXXL = SizedBox(height: xxl, width: xxl);

  static Widget verticalGap(double height) => SizedBox(height: height);
  static Widget horizontalGap(double width) => SizedBox(width: width);
}

/// Border radius constants.
abstract final class AppRadius {
  static const double xs = 4;
  static const double sm = 8;
  static const double md = 12;
  static const double base = 16;
  static const double lg = 20;
  static const double xl = 24;
  static const double circular = 999;

  static const BorderRadius borderXS = BorderRadius.all(Radius.circular(xs));
  static const BorderRadius borderSM = BorderRadius.all(Radius.circular(sm));
  static const BorderRadius borderMD = BorderRadius.all(Radius.circular(md));
  static const BorderRadius borderBase = BorderRadius.all(Radius.circular(base));
  static const BorderRadius borderLG = BorderRadius.all(Radius.circular(lg));
  static const BorderRadius borderXL = BorderRadius.all(Radius.circular(xl));
  static const BorderRadius borderCircular = BorderRadius.all(Radius.circular(circular));
}

/// Elevation constants aligned to Material Design 3 tonal elevation.
abstract final class AppElevation {
  static const double none = 0;
  static const double xs = 1;
  static const double sm = 2;
  static const double md = 4;
  static const double base = 6;
  static const double lg = 8;
  static const double xl = 12;
  static const double modal = 16;
}

/// Animation duration constants for consistent motion across the app.
abstract final class AppDurations {
  /// Instant feedback — button press state changes.
  static const Duration instant = Duration(milliseconds: 100);

  /// Fast micro-interactions — badge pop-ins, icon swaps.
  static const Duration fast = Duration(milliseconds: 200);

  /// Standard transitions — page routes, card reveals.
  static const Duration standard = Duration(milliseconds: 300);

  /// Emphasized transitions — bottom sheets, dialogs.
  static const Duration emphasized = Duration(milliseconds: 400);

  /// Slow reveal animations — onboarding, level-up sequences.
  static const Duration slow = Duration(milliseconds: 600);

  /// Celebration animations — confetti, coin rain.
  static const Duration celebration = Duration(milliseconds: 1500);

  /// Loading shimmer cycle.
  static const Duration shimmer = Duration(milliseconds: 1200);
}
