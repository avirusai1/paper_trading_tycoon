<?php

declare(strict_types=1);

namespace App\Listeners\Portfolio;

use App\Events\PortfolioUpdated;
use App\GameEngine\Contracts\GameEngineContract;
use App\GameEngine\Events\PortfolioSnapshotEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Queued listener for PortfolioUpdated.
 * Relays portfolio snapshots to the Game Engine.
 */
final class HandlePortfolioUpdatedForGame implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly GameEngineContract $gameEngine
    ) {}

    public function handle(PortfolioUpdated $event): void
    {
        Log::info('[HandlePortfolioUpdatedForGame] Relaying portfolio snapshot to Game Engine...', [
            'user_id' => $event->userId,
            'total_value_paise' => $event->totalValuePaise,
        ]);

        try {
            $gameEvent = new PortfolioSnapshotEvent(
                userId: $event->userId,
                snapshotId: uniqid('snap_'),
                totalValuePaise: $event->totalValuePaise
            );

            $this->gameEngine->process($gameEvent);

        } catch (\Throwable $e) {
            Log::error('[HandlePortfolioUpdatedForGame] Failed to handle portfolio update', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
