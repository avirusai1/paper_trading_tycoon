/// Paper Trading Tycoon — App Card
///
/// Base card widget used throughout the app for content grouping.
/// Applies the theme's CardTheme (elevation, corner radius, color).
library;

import 'package:flutter/material.dart';

import '../../../core/theme/app_spacing.dart';

/// A themed card wrapper with optional tap handling and custom padding.
class AppCard extends StatelessWidget {
  const AppCard({
    required this.child,
    super.key,
    this.onTap,
    this.padding = AppSpacing.cardPadding,
    this.margin = EdgeInsets.zero,
  });

  final Widget child;
  final VoidCallback? onTap;
  final EdgeInsets padding;
  final EdgeInsets margin;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: margin,
      child: InkWell(
        onTap: onTap,
        borderRadius: AppRadius.borderMD,
        child: Padding(
          padding: padding,
          child: child,
        ),
      ),
    );
  }
}

/// A card with a gradient background for hero sections (portfolio total, etc.).
class GradientCard extends StatelessWidget {
  const GradientCard({
    required this.child,
    required this.gradient,
    super.key,
    this.padding = AppSpacing.cardPadding,
  });

  final Widget child;
  final Gradient gradient;
  final EdgeInsets padding;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: padding,
      decoration: BoxDecoration(
        gradient: gradient,
        borderRadius: AppRadius.borderBase,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha(38),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: child,
    );
  }
}
