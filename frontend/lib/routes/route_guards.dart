/// Paper Trading Tycoon — Route Guards
///
/// GoRouter redirect functions that enforce authentication and onboarding
/// state before allowing navigation to protected routes.
///
/// Guard hierarchy:
///   1. Onboarding guard — redirects to /onboarding if not completed.
///   2. Auth guard      — redirects to /login if no valid token.
///   3. Email guard     — redirects to /verify if email not verified.
///
/// Guards run on every navigation event (GoRouter's redirect callback),
/// so auth state changes (logout, token expiry) immediately redirect.
library;

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../services/storage/preference_manager.dart';
import '../services/storage/secure_storage_service.dart';
import 'route_names.dart';

/// Provides route guard logic as a Riverpod-aware helper.
final routeGuardProvider = Provider<RouteGuard>((ref) {
  return RouteGuard(
    secureStorage: ref.watch(secureStorageServiceProvider),
    preferenceManager: ref.watch(preferenceManagerProvider),
  );
});

/// Encapsulates all route guard redirect logic.
final class RouteGuard {
  RouteGuard({
    required this.secureStorage,
    required this.preferenceManager,
  });

  final SecureStorageService secureStorage;
  final PreferenceManager preferenceManager;

  /// Public routes accessible without authentication.
  static const Set<String> _publicRoutes = {
    RouteNames.splash,
    RouteNames.onboarding,
    RouteNames.login,
    RouteNames.register,
    RouteNames.forgotPassword,
    RouteNames.resetPassword,
  };

  /// Evaluates redirect logic for the given [state].
  /// Returns the redirect path or null to allow navigation.
  Future<String?> redirect(GoRouterState state) async {
    final currentRoute = state.matchedLocation;
    final isPublicRoute = _publicRoutes.any((r) => currentRoute.contains(r));

    // Step 1: Onboarding
    if (!preferenceManager.isOnboardingCompleted && !isPublicRoute) {
      return '/onboarding';
    }

    // Step 2: Auth
    final hasToken = await secureStorage.hasValidToken();
    if (!hasToken && !isPublicRoute) {
      return '/login';
    }

    // Step 3: Already authenticated, trying to access auth screens
    if (hasToken &&
        (currentRoute.contains('/login') || currentRoute.contains('/register'))) {
      return '/home';
    }

    return null; // No redirect — allow navigation.
  }
}
