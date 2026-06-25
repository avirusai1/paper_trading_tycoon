/// Paper Trading Tycoon — Application Snackbar System
///
/// Centralised snackbar functions ensuring consistent styling and
/// single-snackbar-at-a-time behavior (clears previous before showing).
library;

import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';

/// Shows a success snackbar (green).
void showSuccessSnackbar(BuildContext context, String message) {
  _showSnackbar(context, message: message, backgroundColor: AppColors.success);
}

/// Shows an error snackbar (red).
void showErrorSnackbar(BuildContext context, String message) {
  _showSnackbar(context, message: message, backgroundColor: AppColors.error);
}

/// Shows a neutral informational snackbar.
void showInfoSnackbar(BuildContext context, String message) {
  _showSnackbar(context, message: message);
}

/// Shows a warning snackbar (amber).
void showWarningSnackbar(BuildContext context, String message) {
  _showSnackbar(context, message: message, backgroundColor: AppColors.warning);
}

void _showSnackbar(
  BuildContext context, {
  required String message,
  Color? backgroundColor,
  Duration duration = const Duration(seconds: 3),
}) {
  ScaffoldMessenger.of(context)
    ..clearSnackBars()
    ..showSnackBar(
      SnackBar(
        content: Text(
          message,
          style: const TextStyle(color: Colors.white),
        ),
        backgroundColor: backgroundColor,
        duration: duration,
        behavior: SnackBarBehavior.floating,
        margin: AppSpacing.paddingBase,
        shape: const RoundedRectangleBorder(borderRadius: AppRadius.borderMD),
      ),
    );
}
