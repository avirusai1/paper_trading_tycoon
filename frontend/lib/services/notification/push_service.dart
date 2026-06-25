/// Paper Trading Tycoon — Push Notification Service
///
/// Manages FCM (Firebase Cloud Messaging) token registration and
/// initial notification permission request.
///
/// On startup: requests permission, fetches the FCM token, and
/// registers it with the Laravel API so the backend can deliver
/// personalised push notifications.
///
/// Token refresh: FCM tokens can change; onTokenRefresh automatically
/// re-registers the new token.
library;

import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/constants/api_constants.dart';
import '../../core/utils/logger.dart';
import '../api/api_client.dart';

/// Provides the [PushService] instance.
final pushServiceProvider = Provider<PushService>((ref) {
  return PushService(apiClient: ref.watch(apiClientProvider));
});

/// Manages FCM push notification token lifecycle.
final class PushService {
  PushService({required this.apiClient});

  final ApiClient apiClient;
  final FirebaseMessaging _messaging = FirebaseMessaging.instance;

  /// Requests push notification permission and registers the FCM token.
  /// Should be called once after successful login.
  Future<void> initialize() async {
    final settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    if (settings.authorizationStatus == AuthorizationStatus.denied) {
      AppLogger.info('Push notification permission denied by user.');
      return;
    }

    final token = await _messaging.getToken();
    if (token != null) {
      await _registerToken(token);
    }

    // Listen for token refreshes and re-register.
    _messaging.onTokenRefresh.listen(_registerToken);
  }

  /// Registers or updates the FCM token with the Laravel API.
  Future<void> _registerToken(String token) async {
    try {
      await apiClient.post(
        ApiConstants.registerPushToken,
        body: {'token': token, 'platform': _platform},
      );
      AppLogger.info('FCM token registered with server.');
    } catch (e) {
      AppLogger.warning('Failed to register FCM token.', e);
    }
  }

  String get _platform {
    // Determined at runtime by the Firebase SDK.
    return 'mobile';
  }
}
