<?php
declare(strict_types=1);

namespace App\GameEngine\Events;

use App\GameEngine\Enums\GameEventType;

/**
 * Marker interface for all events that the Game Engine can process.
 *
 * All implementations must be immutable (readonly classes or readonly
 * constructor properties). The Game Engine uses the event type to
 * determine which pipeline stages are applicable.
 */
interface GameEvent
{
    /**
     * The canonical type of this event — used for pipeline routing.
     */
    public function eventType(): GameEventType;

    /**
     * The ID of the user whose game state should be updated.
     */
    public function userId(): int;

    /**
     * A globally unique idempotency key for this event instance.
     * Format: "{EventClass}:{userId}:{sourceId}" per ADR-003.
     */
    public function idempotencyKey(): string;

    /**
     * The source entity ID that triggered this event (trade ID, mission ID,
     * referral ID, etc.). Used as the sourceId in XP logs and coin ledger.
     */
    public function sourceId(): string;
}
