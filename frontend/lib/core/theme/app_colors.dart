/// Paper Trading Tycoon — Color Palette
///
/// Single source of truth for all application colors.
/// All colors are defined here and consumed via AppTheme's ColorScheme.
/// Never use hard-coded Color() values outside this file.
///
/// Color decisions:
/// - Primary: Deep indigo — conveys trust, finance, intelligence.
/// - Secondary: Amber — energy, reward, gamification highlights.
/// - Gain green / Loss red: Standard market convention (Indian market).
/// - Dark theme: Near-black background, elevated surface via tonal layers.
library;

import 'package:flutter/material.dart';

abstract final class AppColors {
  // ── Brand ─────────────────────────────────────────────────────────────────

  /// Primary brand color — used for primary actions, progress indicators.
  static const Color primary = Color(0xFF3D5AFE); // Indigo A400
  static const Color primaryLight = Color(0xFF8187FF);
  static const Color primaryDark = Color(0xFF0031CA);

  /// Secondary / accent color — used for rewards, coins, highlights.
  static const Color secondary = Color(0xFFFFC107); // Amber 500
  static const Color secondaryLight = Color(0xFFFFECB3);
  static const Color secondaryDark = Color(0xFFC79100);

  // ── Market P&L ────────────────────────────────────────────────────────────

  /// Gain color — positive P&L, price increase (Indian market: green).
  static const Color gain = Color(0xFF00C853); // Green A700
  static const Color gainLight = Color(0xFFB9F6CA);

  /// Loss color — negative P&L, price decrease (Indian market: red).
  static const Color loss = Color(0xFFD50000); // Red A700
  static const Color lossLight = Color(0xFFFFCDD2);

  // ── Neutral ───────────────────────────────────────────────────────────────

  static const Color neutral = Color(0xFF9E9E9E); // Grey 500 (unchanged price)

  // ── League Tier Colors ────────────────────────────────────────────────────

  static const Color leagueBronze = Color(0xFFCD7F32);
  static const Color leagueSilver = Color(0xFFC0C0C0);
  static const Color leagueGold = Color(0xFFFFD700);
  static const Color leaguePlatinum = Color(0xFFE5E4E2);
  static const Color leagueDiamond = Color(0xFFB9F2FF);

  // ── XP / Progression ─────────────────────────────────────────────────────

  static const Color xpBar = Color(0xFF00BCD4); // Cyan — XP progress bar

  // ── Coin Economy ─────────────────────────────────────────────────────────

  static const Color coin = Color(0xFFFFAB00); // Amber A700

  // ── Light Theme Surface Colors ────────────────────────────────────────────

  static const Color lightBackground = Color(0xFFF5F5F5);
  static const Color lightSurface = Color(0xFFFFFFFF);
  static const Color lightSurfaceVariant = Color(0xFFEEF0F4);
  static const Color lightOnSurface = Color(0xFF1C1B1F);
  static const Color lightOnSurfaceVariant = Color(0xFF49454F);
  static const Color lightDivider = Color(0xFFE0E0E0);

  // ── Dark Theme Surface Colors ─────────────────────────────────────────────

  static const Color darkBackground = Color(0xFF0F1117); // Deep dark
  static const Color darkSurface = Color(0xFF1A1D27); // Card background
  static const Color darkSurfaceVariant = Color(0xFF252836); // Elevated card
  static const Color darkOnSurface = Color(0xFFE6E1E5);
  static const Color darkOnSurfaceVariant = Color(0xFFCAC4D0);
  static const Color darkDivider = Color(0xFF2C2F3E);

  // ── Status Colors ─────────────────────────────────────────────────────────

  static const Color success = Color(0xFF43A047); // Green 600
  static const Color warning = Color(0xFFFF6F00); // Amber 900
  static const Color error = Color(0xFFB00020); // Custom red
  static const Color info = Color(0xFF1565C0); // Blue 800

  // ── Gradient Definitions ──────────────────────────────────────────────────

  /// Primary gradient used in the app bar and hero sections.
  static const LinearGradient primaryGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [primaryDark, primary, primaryLight],
  );

  /// Gradient for gain indicators.
  static const LinearGradient gainGradient = LinearGradient(
    colors: [Color(0xFF00C853), Color(0xFF69F0AE)],
  );

  /// Gradient for loss indicators.
  static const LinearGradient lossGradient = LinearGradient(
    colors: [Color(0xFFD50000), Color(0xFFFF5252)],
  );

  /// Gold gradient for premium and reward highlights.
  static const LinearGradient goldGradient = LinearGradient(
    colors: [Color(0xFFC79100), Color(0xFFFFD740), Color(0xFFC79100)],
  );
}
