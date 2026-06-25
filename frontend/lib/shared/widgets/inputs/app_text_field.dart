/// Paper Trading Tycoon — App Text Field
///
/// Standardised text input widget built on Flutter's [TextFormField].
/// Applies the global InputDecorationTheme from [AppTheme] and adds
/// consistent semantics, accessibility labels, and optional prefix/suffix icons.
library;

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

/// Reusable form text field with consistent theming and validation support.
class AppTextField extends StatelessWidget {
  const AppTextField({
    required this.label,
    super.key,
    this.controller,
    this.hint,
    this.validator,
    this.keyboardType = TextInputType.text,
    this.textInputAction = TextInputAction.next,
    this.obscureText = false,
    this.prefixIcon,
    this.suffixIcon,
    this.onChanged,
    this.onFieldSubmitted,
    this.enabled = true,
    this.autofillHints,
    this.inputFormatters,
    this.maxLines = 1,
    this.maxLength,
    this.focusNode,
    this.autofocus = false,
  });

  final String label;
  final String? hint;
  final TextEditingController? controller;
  final String? Function(String?)? validator;
  final TextInputType keyboardType;
  final TextInputAction textInputAction;
  final bool obscureText;
  final Widget? prefixIcon;
  final Widget? suffixIcon;
  final ValueChanged<String>? onChanged;
  final ValueChanged<String>? onFieldSubmitted;
  final bool enabled;
  final Iterable<String>? autofillHints;
  final List<TextInputFormatter>? inputFormatters;
  final int? maxLines;
  final int? maxLength;
  final FocusNode? focusNode;
  final bool autofocus;

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      focusNode: focusNode,
      autofocus: autofocus,
      decoration: InputDecoration(
        labelText: label,
        hintText: hint,
        prefixIcon: prefixIcon,
        suffixIcon: suffixIcon,
        counterText: '',
      ),
      keyboardType: keyboardType,
      textInputAction: textInputAction,
      obscureText: obscureText,
      validator: validator,
      onChanged: onChanged,
      onFieldSubmitted: onFieldSubmitted,
      enabled: enabled,
      autofillHints: autofillHints,
      inputFormatters: inputFormatters,
      maxLines: obscureText ? 1 : maxLines,
      maxLength: maxLength,
    );
  }
}
