/// Paper Trading Tycoon — Application Router
///
/// Configures the GoRouter with:
///   - Shell route for the main tab navigator (home, market, portfolio, leaderboard, profile).
///   - Public routes (splash, onboarding, auth).
///   - Protected routes (all main-app screens).
///   - Deep link placeholders for push notification navigation.
///   - Auth redirect guard via [RouteGuard].
///
/// Screens are imported as empty-shell classes during the foundation phase.
/// Business screens are implemented per milestone.
library;

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import 'route_guards.dart';
import 'route_names.dart';

/// Provides the configured [GoRouter] instance.
/// Re-created when [routeGuardProvider] changes (e.g. on logout).
final appRouterProvider = Provider<GoRouter>((ref) {
  final guard = ref.watch(routeGuardProvider);

  return GoRouter(
    initialLocation: '/splash',
    debugLogDiagnostics: false,
    redirect: guard.redirect,

    routes: [
      // ── Splash ────────────────────────────────────────────────────────────
      GoRoute(
        path: '/splash',
        name: RouteNames.splash,
        builder: (context, state) => const _PlaceholderScreen(name: 'Splash'),
      ),

      // ── Onboarding ────────────────────────────────────────────────────────
      GoRoute(
        path: '/onboarding',
        name: RouteNames.onboarding,
        builder: (context, state) => const _PlaceholderScreen(name: 'Onboarding'),
      ),

      // ── Authentication ────────────────────────────────────────────────────
      GoRoute(
        path: '/login',
        name: RouteNames.login,
        builder: (context, state) => const _PlaceholderScreen(name: 'Login'),
        routes: [
          GoRoute(
            path: 'forgot-password',
            name: RouteNames.forgotPassword,
            builder: (context, state) => const _PlaceholderScreen(name: 'Forgot Password'),
          ),
        ],
      ),
      GoRoute(
        path: '/register',
        name: RouteNames.register,
        builder: (context, state) => const _PlaceholderScreen(name: 'Register'),
      ),
      GoRoute(
        path: '/verify-email',
        name: RouteNames.emailVerification,
        builder: (context, state) => const _PlaceholderScreen(name: 'Email Verification'),
      ),
      GoRoute(
        path: '/reset-password',
        name: RouteNames.resetPassword,
        builder: (context, state) => const _PlaceholderScreen(name: 'Reset Password'),
      ),

      // ── Main App Shell ────────────────────────────────────────────────────
      ShellRoute(
        builder: (context, state, child) => _MainShell(child: child),
        routes: [
          GoRoute(
            path: '/home',
            name: RouteNames.home,
            builder: (context, state) => const _PlaceholderScreen(name: 'Home'),
          ),
          GoRoute(
            path: '/market',
            name: RouteNames.stockMarket,
            builder: (context, state) => const _PlaceholderScreen(name: 'Market'),
            routes: [
              GoRoute(
                path: 'search',
                name: RouteNames.stockSearch,
                builder: (context, state) => const _PlaceholderScreen(name: 'Stock Search'),
              ),
              GoRoute(
                path: 'watchlist',
                name: RouteNames.watchlist,
                builder: (context, state) => const _PlaceholderScreen(name: 'Watchlist'),
              ),
              GoRoute(
                path: ':symbol',
                name: RouteNames.stockDetail,
                builder: (context, state) {
                  final symbol = state.pathParameters['symbol'] ?? '';
                  return _PlaceholderScreen(name: 'Stock Detail: $symbol');
                },
                routes: [
                  GoRoute(
                    path: 'buy',
                    name: RouteNames.buyOrder,
                    builder: (context, state) => const _PlaceholderScreen(name: 'Buy Order'),
                  ),
                  GoRoute(
                    path: 'sell',
                    name: RouteNames.sellOrder,
                    builder: (context, state) => const _PlaceholderScreen(name: 'Sell Order'),
                  ),
                ],
              ),
            ],
          ),
          GoRoute(
            path: '/portfolio',
            name: RouteNames.portfolio,
            builder: (context, state) => const _PlaceholderScreen(name: 'Portfolio'),
            routes: [
              GoRoute(
                path: 'history',
                name: RouteNames.tradeHistory,
                builder: (context, state) => const _PlaceholderScreen(name: 'Trade History'),
              ),
            ],
          ),
          GoRoute(
            path: '/leaderboards',
            name: RouteNames.leaderboards,
            builder: (context, state) => const _PlaceholderScreen(name: 'Leaderboards'),
          ),
          GoRoute(
            path: '/profile',
            name: RouteNames.profile,
            builder: (context, state) => const _PlaceholderScreen(name: 'Profile'),
            routes: [
              GoRoute(
                path: 'edit',
                name: RouteNames.editProfile,
                builder: (context, state) => const _PlaceholderScreen(name: 'Edit Profile'),
              ),
            ],
          ),
        ],
      ),

      // ── Out-of-shell screens ──────────────────────────────────────────────
      GoRoute(
        path: '/achievements',
        name: RouteNames.achievements,
        builder: (context, state) => const _PlaceholderScreen(name: 'Achievements'),
      ),
      GoRoute(
        path: '/challenges',
        name: RouteNames.challenges,
        builder: (context, state) => const _PlaceholderScreen(name: 'Challenges'),
      ),
      GoRoute(
        path: '/store',
        name: RouteNames.store,
        builder: (context, state) => const _PlaceholderScreen(name: 'Store'),
      ),
      GoRoute(
        path: '/premium',
        name: RouteNames.premium,
        builder: (context, state) => const _PlaceholderScreen(name: 'Premium'),
      ),
      GoRoute(
        path: '/notifications',
        name: RouteNames.notifications,
        builder: (context, state) => const _PlaceholderScreen(name: 'Notifications'),
      ),
      GoRoute(
        path: '/referral',
        name: RouteNames.referral,
        builder: (context, state) => const _PlaceholderScreen(name: 'Referral'),
      ),
      GoRoute(
        path: '/settings',
        name: RouteNames.settings,
        builder: (context, state) => const _PlaceholderScreen(name: 'Settings'),
      ),
      GoRoute(
        path: '/users/:userId',
        name: RouteNames.publicProfile,
        builder: (context, state) {
          final userId = state.pathParameters['userId'] ?? '';
          return _PlaceholderScreen(name: 'User Profile: $userId');
        },
      ),
      GoRoute(
        path: '/order-success',
        name: RouteNames.orderSuccess,
        builder: (context, state) => const _PlaceholderScreen(name: 'Order Success'),
      ),
      GoRoute(
        path: '/level-up',
        name: RouteNames.levelUp,
        builder: (context, state) => const _PlaceholderScreen(name: 'Level Up'),
      ),
    ],

    errorBuilder: (context, state) => Scaffold(
      body: Center(
        child: Text('Page not found: ${state.uri.path}'),
      ),
    ),
  );
});

// ── Shell placeholder (replaced in Milestone 2+) ─────────────────────────────

/// Main app bottom navigation shell.
/// Replaced with the real BottomNavigationBar shell in Milestone 2.
class _MainShell extends StatelessWidget {
  const _MainShell({required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) => child;
}

/// Route placeholder used during the foundation phase.
/// Every route has a compilable non-empty screen from day 1.
class _PlaceholderScreen extends StatelessWidget {
  const _PlaceholderScreen({required this.name});

  final String name;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(name)),
      body: Center(
        child: Text(
          name,
          style: Theme.of(context).textTheme.headlineMedium,
        ),
      ),
    );
  }
}
