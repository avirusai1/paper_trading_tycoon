<?php
declare(strict_types=1);

namespace App\GameEngine\Contracts;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\GameResult;
use App\GameEngine\Events\GameEvent;

/**
 * Central Game Engine contract.
 *
 * The Game Engine receives a GameEvent, builds the full GameContext for the
 * affected user, runs every applicable pipeline (XP → Level → Career →
 * Mission → Achievement → League → Season → Rewards), persists all state
 * transitions atomically, and publishes the resulting domain events.
 *
 * The Game Engine is completely independent of HTTP. It has no knowledge of
 * controllers, requests, or responses.
 */
interface GameEngineContract
{
    /**
     * Process a single gameplay event for a user.
     *
     * The returned GameResult is a complete record of every state change that
     * occurred as a result of this event (XP gained, level-ups, missions
     * progressed, achievements unlocked, coins awarded, etc.).
     *
     * @throws \App\GameEngine\Exceptions\GameEngineException
     */
    public function process(GameEvent $event): GameResult;

    /**
     * Build the full GameContext for a user without processing an event.
     * Used by APIs to return current player state to the client.
     *
     * @throws \App\GameEngine\Exceptions\GameEngineException
     */
    public function buildContext(int $userId): GameContext;
}
