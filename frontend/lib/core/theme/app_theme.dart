/// Paper Trading Tycoon — Application Theme
///
/// Constructs the Material Design 3 [ThemeData] for both light and dark modes.
/// All visual tokens (colors, typography, shapes) originate from the design
/// system files in this folder — never hardcoded in individual widgets.
///
/// The active [ThemeMode] is driven by user preferences stored in Hive
/// and exposed via [themeModeProvider].
library;

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app_colors.dart';
import 'app_spacing.dart';
import 'app_typography.dart';

/// Riverpod provider for the user's chosen [ThemeMode].
/// Reads from [PreferenceManager] and defaults to [ThemeMode.system].
final themeModeProvider = StateProvider<ThemeMode>((ref) => ThemeMode.system);

abstract final class AppTheme {
  // ── Light Theme ───────────────────────────────────────────────────────────

  static ThemeData get lightTheme {
    const colorScheme = ColorScheme.light(
      primary: AppColors.primary,
      onPrimary: Colors.white,
      primaryContainer: AppColors.primaryLight,
      onPrimaryContainer: AppColors.primaryDark,
      secondary: AppColors.secondary,
      onSecondary: Colors.black,
      secondaryContainer: AppColors.secondaryLight,
      onSecondaryContainer: AppColors.secondaryDark,
      error: AppColors.error,
      onError: Colors.white,
      surface: AppColors.lightSurface,
      onSurface: AppColors.lightOnSurface,
      surfaceContainerHighest: AppColors.lightSurfaceVariant,
      onSurfaceVariant: AppColors.lightOnSurfaceVariant,
      outline: AppColors.lightDivider,
      brightness: Brightness.light,
    );

    return _buildTheme(colorScheme);
  }

  // ── Dark Theme ────────────────────────────────────────────────────────────

  static ThemeData get darkTheme {
    const colorScheme = ColorScheme.dark(
      primary: AppColors.primaryLight,
      onPrimary: AppColors.primaryDark,
      primaryContainer: AppColors.primaryDark,
      onPrimaryContainer: AppColors.primaryLight,
      secondary: AppColors.secondary,
      onSecondary: Colors.black,
      secondaryContainer: AppColors.secondaryDark,
      onSecondaryContainer: AppColors.secondaryLight,
      error: Color(0xFFCF6679),
      onError: Color(0xFF370007),
      surface: AppColors.darkSurface,
      onSurface: AppColors.darkOnSurface,
      surfaceContainerHighest: AppColors.darkSurfaceVariant,
      onSurfaceVariant: AppColors.darkOnSurfaceVariant,
      outline: AppColors.darkDivider,
      brightness: Brightness.dark,
    );

    return _buildTheme(colorScheme);
  }

  // ── Shared Theme Builder ──────────────────────────────────────────────────

  static ThemeData _buildTheme(ColorScheme colorScheme) {
    return ThemeData(
      useMaterial3: true,
      colorScheme: colorScheme,
      textTheme: AppTypography.textTheme.apply(
        bodyColor: colorScheme.onSurface,
        displayColor: colorScheme.onSurface,
      ),

      // AppBar
      appBarTheme: AppBarTheme(
        backgroundColor: colorScheme.surface,
        foregroundColor: colorScheme.onSurface,
        elevation: AppElevation.none,
        scrolledUnderElevation: AppElevation.xs,
        centerTitle: true,
        titleTextStyle: AppTypography.titleMedium.copyWith(
          color: colorScheme.onSurface,
        ),
      ),

      // Cards
      cardTheme: CardThemeData(
        color: colorScheme.surface,
        elevation: AppElevation.xs,
        shape: const RoundedRectangleBorder(borderRadius: AppRadius.borderMD),
        margin: EdgeInsets.zero,
      ),

      // Elevated Button
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: colorScheme.primary,
          foregroundColor: colorScheme.onPrimary,
          minimumSize: const Size(double.infinity, 52),
          shape: const RoundedRectangleBorder(borderRadius: AppRadius.borderMD),
          textStyle: AppTypography.labelLarge,
          elevation: AppElevation.none,
        ),
      ),

      // Outlined Button
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: colorScheme.primary,
          minimumSize: const Size(double.infinity, 52),
          shape: const RoundedRectangleBorder(borderRadius: AppRadius.borderMD),
          side: BorderSide(color: colorScheme.primary),
          textStyle: AppTypography.labelLarge,
        ),
      ),

      // Text Button
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: colorScheme.primary,
          textStyle: AppTypography.labelLarge,
        ),
      ),

      // Input Decoration
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: colorScheme.surfaceContainerHighest,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: AppSpacing.base,
          vertical: AppSpacing.md,
        ),
        border: const OutlineInputBorder(
          borderRadius: AppRadius.borderMD,
          borderSide: BorderSide.none,
        ),
        enabledBorder: const OutlineInputBorder(
          borderRadius: AppRadius.borderMD,
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: AppRadius.borderMD,
          borderSide: BorderSide(color: colorScheme.primary, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: AppRadius.borderMD,
          borderSide: BorderSide(color: colorScheme.error),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: AppRadius.borderMD,
          borderSide: BorderSide(color: colorScheme.error, width: 2),
        ),
        labelStyle: AppTypography.bodyMedium.copyWith(
          color: colorScheme.onSurfaceVariant,
        ),
        errorStyle: AppTypography.bodySmall.copyWith(color: colorScheme.error),
      ),

      // Bottom Navigation Bar
      bottomNavigationBarTheme: BottomNavigationBarThemeData(
        backgroundColor: colorScheme.surface,
        selectedItemColor: colorScheme.primary,
        unselectedItemColor: colorScheme.onSurfaceVariant,
        type: BottomNavigationBarType.fixed,
        elevation: AppElevation.md,
        showSelectedLabels: true,
        showUnselectedLabels: true,
        selectedLabelStyle: AppTypography.labelSmall,
        unselectedLabelStyle: AppTypography.labelSmall,
      ),

      // Divider
      dividerTheme: DividerThemeData(
        color: colorScheme.outline,
        thickness: 1,
        space: 1,
      ),

      // Bottom Sheet
      bottomSheetTheme: const BottomSheetThemeData(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
        ),
        showDragHandle: true,
      ),

      // Chip
      chipTheme: ChipThemeData(
        shape: RoundedRectangleBorder(borderRadius: AppRadius.borderSM),
        labelStyle: AppTypography.labelMedium,
      ),

      // Snack Bar
      snackBarTheme: SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        shape: const RoundedRectangleBorder(borderRadius: AppRadius.borderMD),
        contentTextStyle: AppTypography.bodyMedium.copyWith(color: Colors.white),
      ),
    );
  }
}
