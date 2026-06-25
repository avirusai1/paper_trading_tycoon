/// Paper Trading Tycoon — Secondary Button
///
/// Outlined button for secondary actions (cancel, go back, view more).
library;

import 'package:flutter/material.dart';

import '../../../core/theme/app_spacing.dart';

/// Full-width outlined secondary action button.
class SecondaryButton extends StatelessWidget {
  const SecondaryButton({
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
      child: OutlinedButton(
        onPressed: isActive ? onPressed : null,
        child: isLoading
            ? SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  valueColor: AlwaysStoppedAnimation<Color>(
                    Theme.of(context).colorScheme.primary,
                  ),
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
