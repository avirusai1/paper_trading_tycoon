<?php

declare(strict_types=1);

/**
 * Paper Trading Tycoon — API Routes
 *
 * All routes are versioned under /api/v1/.
 * Route groups mirror the module structure from 00_MASTER_ARCHITECTURE.md.
 *
 * Middleware:
 *   - 'api'    → applies throttle:api and bindings
 *   - 'auth:sanctum' → requires valid Sanctum token
 *   - 'verified' → requires email verification (applied to trading/portfolio)
 *
 * Controllers are registered here but implemented per milestone.
 */

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FeatureFlagController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

// ── System ─────────────────────────────────────────────────────────────────

Route::get('/health', HealthController::class)->name('health');

// ── API v1 ─────────────────────────────────────────────────────────────────

Route::prefix('v1')->name('v1.')->group(function (): void {

    // ── Feature Flags (public — no auth required) ─────────────────────────
    Route::get('/feature-flags', FeatureFlagController::class)->name('feature-flags');

    // ── Authentication ─────────────────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/password/forgot', [AuthController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('reset-password');

        // Authenticated auth routes
        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
            Route::post('/email/resend', [AuthController::class, 'resendVerification'])->name('email.resend');
            Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('email.verify');
        });
    });

    // ── Authenticated Routes ───────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function (): void {

        // ── User Profile ──────────────────────────────────────────────────
        Route::prefix('profile')->name('profile.')->group(function (): void {
            // Routes defined in Milestone 2
        });

        // ── Stock Market ──────────────────────────────────────────────────
        Route::prefix('stocks')->name('stocks.')->group(function (): void {
            // Routes defined in Milestone 3
        });

        // ── Watchlist ─────────────────────────────────────────────────────
        Route::prefix('watchlist')->name('watchlist.')->group(function (): void {
            // Routes defined in Milestone 3
        });

        // ── Trading (requires email verification) ─────────────────────────
        Route::middleware('verified')->prefix('trades')->name('trades.')->group(function (): void {
            // Routes defined in Milestone 4
        });

        // ── Portfolio ─────────────────────────────────────────────────────
        Route::prefix('portfolio')->name('portfolio.')->group(function (): void {
            // Routes defined in Milestone 4
        });

        // ── Game State ────────────────────────────────────────────────────
        Route::prefix('game')->name('game.')->group(function (): void {
            // Routes defined in Milestone 5
        });

        // ── Achievements ──────────────────────────────────────────────────
        Route::prefix('achievements')->name('achievements.')->group(function (): void {
            // Routes defined in Milestone 6
        });

        // ── Challenges ────────────────────────────────────────────────────
        Route::prefix('challenges')->name('challenges.')->group(function (): void {
            // Routes defined in Milestone 6
        });

        // ── Leaderboards ──────────────────────────────────────────────────
        Route::prefix('leaderboards')->name('leaderboards.')->group(function (): void {
            // Routes defined in Milestone 7
        });

        // ── Coin Economy ──────────────────────────────────────────────────
        Route::prefix('economy')->name('economy.')->group(function (): void {
            // Routes defined in Milestone 8
        });

        // ── Store ─────────────────────────────────────────────────────────
        Route::prefix('store')->name('store.')->group(function (): void {
            // Routes defined in Milestone 8
        });

        // ── Premium ───────────────────────────────────────────────────────
        Route::prefix('premium')->name('premium.')->group(function (): void {
            // Routes defined in Milestone 11
        });

        // ── Notifications ─────────────────────────────────────────────────
        Route::prefix('notifications')->name('notifications.')->group(function (): void {
            // Routes defined in Milestone 9
        });

        // ── Referral ──────────────────────────────────────────────────────
        Route::prefix('referral')->name('referral.')->group(function (): void {
            // Routes defined in Milestone 10
        });

        // ── Settings ──────────────────────────────────────────────────────
        Route::prefix('settings')->name('settings.')->group(function (): void {
            // Routes defined in Milestone 2
        });
    });
});
