<?php
declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * Dispatched when a new user completes registration.
 * Listeners: HandleUserRegisteredAnalytics
 */
final class UserRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int     $userId,
        public readonly ?string $referralCode,
        public readonly Carbon  $timestamp,
    ) {}
}
