/// Paper Trading Tycoon — Primary Button
///
/// The main call-to-action button used across the app (buy, confirm, submit).
/// Handles loading states internally — callers set [isLoading] to show a
/// spinner during async operations without managing separate state.
library;

import 'package:flutter/material.dart';

import '../../../core/theme/app_spacing.dart';

/// Full-width primary action button with built-in loading state.
class PrimaryButton extends StatelessWidget {
  const PrimaryButton({
    required this.label,
    required this.onPressed,
    super.key,
    this.isLoading = false,
    this.isEnabled = true,
    this.icon,
    this.width = double.infinity,
    this.height = 52,
  });

  final String label;
  final VoidCallback? onPressed;
  final bool isLoading;
  final bool isEnabled;
  final IconData? icon;
  final double width;
  final double height;

  @override
  Widget build(BuildContext context) {
    final isActive = isEnabled && !isLoading;

    return SizedBox(
      width: width,
      height: height,
      child: ElevatedButton(
        onPressed: isActive ? onPressed : null,
        child: isLoading
            ? const SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                ),
              )
            : Row(
                mainAxisAlignment: MainAxisAlignment.center,
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (icon != null) ...[
                    Icon(icon, size: 18),
                    AppSpacing.horizontalGap(AppSpacing.sm),
                  ],
                  Text(label),
                ],
              ),
      ),
    );
  }
}
