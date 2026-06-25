/// Paper Trading Tycoon — Error State Widget
///
/// Reusable error display used when a screen fails to load data.
/// Shows an icon, message, and optional retry button.
library;

import 'package:flutter/material.dart';

import '../../../core/errors/failures.dart';
import '../../../core/theme/app_spacing.dart';
import '../buttons/primary_button.dart';

/// Displays a user-friendly error state with an optional retry action.
class ErrorStateWidget extends StatelessWidget {
  const ErrorStateWidget({
    required this.failure,
    super.key,
    this.onRetry,
    this.title,
  });

  /// The [Failure] from the domain layer — [message] is shown to the user.
  final Failure failure;

  /// Optional retry callback. If provided, a retry button is shown.
  final VoidCallback? onRetry;

  /// Optional custom title. Defaults to 'Something went wrong'.
  final String? title;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: AppSpacing.paddingXL,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              _iconForFailure(failure),
              size: 64,
              color: Theme.of(context).colorScheme.error,
            ),
            AppSpacing.verticalGap(AppSpacing.base),
            Text(
              title ?? 'Something went wrong',
              style: Theme.of(context).textTheme.titleMedium,
              textAlign: TextAlign.center,
            ),
            AppSpacing.verticalGap(AppSpacing.sm),
            Text(
              failure.message,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Theme.of(context).colorScheme.onSurfaceVariant,
              ),
              textAlign: TextAlign.center,
            ),
            if (onRetry != null) ...[
              AppSpacing.verticalGap(AppSpacing.xl),
              PrimaryButton(
                label: 'Try Again',
                onPressed: onRetry,
                width: 200,
              ),
            ],
          ],
        ),
      ),
    );
  }

  IconData _iconForFailure(Failure failure) {
    return switch (failure) {
      NetworkFailure() => Icons.wifi_off_rounded,
      TimeoutFailure() => Icons.timer_off_rounded,
      UnauthorizedFailure() => Icons.lock_outline_rounded,
      NotFoundFailure() => Icons.search_off_rounded,
      ServerFailure() => Icons.cloud_off_rounded,
      _ => Icons.error_outline_rounded,
    };
  }
}
