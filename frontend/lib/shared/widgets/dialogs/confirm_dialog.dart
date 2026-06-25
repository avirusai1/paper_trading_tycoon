/// Paper Trading Tycoon — Confirmation Dialog
///
/// Reusable dialog for destructive or significant actions that require
/// explicit user confirmation (e.g. confirm trade, delete account).
library;

import 'package:flutter/material.dart';

import '../../../core/theme/app_spacing.dart';

/// Shows a confirmation dialog and returns true if the user confirms.
/// Returns false or null if the user cancels or dismisses.
Future<bool?> showConfirmDialog({
  required BuildContext context,
  required String title,
  required String message,
  String confirmLabel = 'Confirm',
  String cancelLabel = 'Cancel',
  bool isDestructive = false,
}) {
  return showDialog<bool>(
    context: context,
    builder: (context) => ConfirmDialog(
      title: title,
      message: message,
      confirmLabel: confirmLabel,
      cancelLabel: cancelLabel,
      isDestructive: isDestructive,
    ),
  );
}

/// A reusable Material confirmation dialog.
class ConfirmDialog extends StatelessWidget {
  const ConfirmDialog({
    required this.title,
    required this.message,
    super.key,
    this.confirmLabel = 'Confirm',
    this.cancelLabel = 'Cancel',
    this.isDestructive = false,
  });

  final String title;
  final String message;
  final String confirmLabel;
  final String cancelLabel;

  /// When true, the confirm button uses the error color (for delete/irreversible actions).
  final bool isDestructive;

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;

    return AlertDialog(
      title: Text(title),
      content: Text(message),
      contentPadding: AppSpacing.cardPadding,
      actionsPadding: const EdgeInsets.fromLTRB(
        AppSpacing.base,
        AppSpacing.xs,
        AppSpacing.base,
        AppSpacing.base,
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(false),
          child: Text(cancelLabel),
        ),
        ElevatedButton(
          onPressed: () => Navigator.of(context).pop(true),
          style: ElevatedButton.styleFrom(
            backgroundColor: isDestructive ? colorScheme.error : colorScheme.primary,
            foregroundColor: isDestructive ? colorScheme.onError : colorScheme.onPrimary,
          ),
          child: Text(confirmLabel),
        ),
      ],
    );
  }
}
