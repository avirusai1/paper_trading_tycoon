/// Paper Trading Tycoon — Empty State Widget
///
/// Reusable empty state for lists and screens with no content yet.
/// Used when data loads successfully but returns no results.
library;

import 'package:flutter/material.dart';

import '../../../core/theme/app_spacing.dart';
import '../buttons/primary_button.dart';

/// Displays a friendly empty state with icon, title, message, and optional CTA.
class EmptyStateWidget extends StatelessWidget {
  const EmptyStateWidget({
    required this.title,
    required this.message,
    super.key,
    this.icon,
    this.ctaLabel,
    this.onCta,
  });

  final String title;
  final String message;
  final IconData? icon;
  final String? ctaLabel;
  final VoidCallback? onCta;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: AppSpacing.paddingXL,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (icon != null)
              Icon(
                icon,
                size: 72,
                color: Theme.of(context).colorScheme.onSurfaceVariant.withAlpha(128),
              ),
            AppSpacing.verticalGap(AppSpacing.base),
            Text(
              title,
              style: Theme.of(context).textTheme.titleLarge,
              textAlign: TextAlign.center,
            ),
            AppSpacing.verticalGap(AppSpacing.sm),
            Text(
              message,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Theme.of(context).colorScheme.onSurfaceVariant,
              ),
              textAlign: TextAlign.center,
            ),
            if (ctaLabel != null && onCta != null) ...[
              AppSpacing.verticalGap(AppSpacing.xl),
              PrimaryButton(
                label: ctaLabel!,
                onPressed: onCta,
                width: 220,
              ),
            ],
          ],
        ),
      ),
    );
  }
}
