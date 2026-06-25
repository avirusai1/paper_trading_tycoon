/// Paper Trading Tycoon — Form Validators
///
/// Pure functions for validating user input in forms.
/// These validators are for client-side UX feedback only.
/// All input is validated authoritatively by the Laravel API.
library;

/// Collection of reusable form field validators.
abstract final class Validators {
  // ── Email ─────────────────────────────────────────────────────────────────

  static const String _emailPattern =
      r'^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$';

  /// Validates an email address.
  /// Returns an error string or null if valid.
  static String? email(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Email address is required.';
    }
    if (!RegExp(_emailPattern).hasMatch(value.trim())) {
      return 'Enter a valid email address.';
    }
    return null;
  }

  // ── Password ──────────────────────────────────────────────────────────────

  /// Validates a password for registration.
  /// Minimum 8 characters, at least one letter and one number.
  static String? password(String? value) {
    if (value == null || value.isEmpty) {
      return 'Password is required.';
    }
    if (value.length < 8) {
      return 'Password must be at least 8 characters.';
    }
    if (!RegExp(r'[a-zA-Z]').hasMatch(value)) {
      return 'Password must contain at least one letter.';
    }
    if (!RegExp(r'[0-9]').hasMatch(value)) {
      return 'Password must contain at least one number.';
    }
    return null;
  }

  /// Validates a password confirmation field against the original.
  static String? confirmPassword(String? value, String original) {
    if (value == null || value.isEmpty) {
      return 'Please confirm your password.';
    }
    if (value != original) {
      return 'Passwords do not match.';
    }
    return null;
  }

  // ── Display Name ──────────────────────────────────────────────────────────

  /// Validates a user display name.
  static String? displayName(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Display name is required.';
    }
    if (value.trim().length < 3) {
      return 'Display name must be at least 3 characters.';
    }
    if (value.trim().length > 30) {
      return 'Display name cannot exceed 30 characters.';
    }
    return null;
  }

  // ── Trade Quantity ────────────────────────────────────────────────────────

  /// Validates a trade quantity input.
  /// [maxQuantity] is the maximum the user can buy/sell.
  static String? tradeQuantity(String? value, {required int maxQuantity}) {
    if (value == null || value.trim().isEmpty) {
      return 'Quantity is required.';
    }
    final parsed = int.tryParse(value.trim());
    if (parsed == null) {
      return 'Enter a valid whole number.';
    }
    if (parsed <= 0) {
      return 'Quantity must be greater than 0.';
    }
    if (parsed > maxQuantity) {
      return 'Maximum quantity is $maxQuantity.';
    }
    return null;
  }

  // ── Generic ───────────────────────────────────────────────────────────────

  /// Validates a required text field with an optional custom label.
  static String? required(String? value, {String fieldName = 'This field'}) {
    if (value == null || value.trim().isEmpty) {
      return '$fieldName is required.';
    }
    return null;
  }

  /// Validates that a phone number is a 10-digit Indian mobile number.
  static String? indianPhoneNumber(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Phone number is required.';
    }
    final digits = value.replaceAll(RegExp(r'\D'), '');
    if (digits.length != 10) {
      return 'Enter a valid 10-digit mobile number.';
    }
    if (!RegExp(r'^[6-9]\d{9}$').hasMatch(digits)) {
      return 'Enter a valid Indian mobile number.';
    }
    return null;
  }
}
