/// Paper Trading Tycoon — Loading Indicators
///
/// Reusable loading state widgets for consistent UX across all async operations.
library;

import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';

import '../../../core/theme/app_spacing.dart';

/// Centered circular progress indicator — used for full-screen loading.
class AppLoadingIndicator extends StatelessWidget {
  const AppLoadingIndicator({super.key, this.size = 40});

  final double size;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: SizedBox(
        width: size,
        height: size,
        child: CircularProgressIndicator(
          strokeWidth: 3,
          valueColor: AlwaysStoppedAnimation<Color>(
            Theme.of(context).colorScheme.primary,
          ),
        ),
      ),
    );
  }
}

/// Full-screen loading overlay used during blocking async operations.
class FullScreenLoader extends StatelessWidget {
  const FullScreenLoader({super.key, this.message});

  final String? message;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const AppLoadingIndicator(),
            if (message != null) ...[
              AppSpacing.verticalGap(AppSpacing.base),
              Text(
                message!,
                style: Theme.of(context).textTheme.bodyMedium,
                textAlign: TextAlign.center,
              ),
            ],
          ],
        ),
      ),
    );
  }
}

/// Shimmer placeholder for list items during initial data fetch.
class ShimmerListTile extends StatelessWidget {
  const ShimmerListTile({super.key, this.height = 72});

  final double height;

  @override
  Widget build(BuildContext context) {
    final baseColor = Theme.of(context).colorScheme.surfaceContainerHighest;
    final highlightColor = Theme.of(context).colorScheme.surface;

    return Shimmer.fromColors(
      baseColor: baseColor,
      highlightColor: highlightColor,
      child: Container(
        height: height,
        margin: const EdgeInsets.symmetric(
          horizontal: AppSpacing.base,
          vertical: AppSpacing.xs,
        ),
        decoration: BoxDecoration(
          color: baseColor,
          borderRadius: AppRadius.borderMD,
        ),
      ),
    );
  }
}

/// Shimmer placeholder for cards during data fetch.
class ShimmerCard extends StatelessWidget {
  const ShimmerCard({super.key, this.height = 120, this.width = double.infinity});

  final double height;
  final double width;

  @override
  Widget build(BuildContext context) {
    final baseColor = Theme.of(context).colorScheme.surfaceContainerHighest;
    final highlightColor = Theme.of(context).colorScheme.surface;

    return Shimmer.fromColors(
      baseColor: baseColor,
      highlightColor: highlightColor,
      child: Container(
        height: height,
        width: width,
        margin: AppSpacing.paddingXS,
        decoration: BoxDecoration(
          color: baseColor,
          borderRadius: AppRadius.borderMD,
        ),
      ),
    );
  }
}
