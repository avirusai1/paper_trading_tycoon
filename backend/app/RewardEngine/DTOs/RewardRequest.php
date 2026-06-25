<?php
declare(strict_types=1);

namespace App\RewardEngine\DTOs;

use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Enums\RewardType;

/**
 * Immutable value object representing a single reward distribution request.
 *
 * The idempotencyKey MUST be globally unique per real-world reward event.
 * Convention: "{source_value}:{source_id}:{user_id}"
 * e.g. "mission:42:7", "achievement:bronze_first_trade:7"
 *
 * All DB writes keyed to this value — duplicate keys are silently no-op'd.
 */
final readonly class RewardRequest
{
    /**
     * @param  int              $userId          Target player.
     * @param  RewardType       $rewardType      What is being granted.
     * @param  RewardSource     $source          Which system originated the reward.
     * @param  string           $sourceId        Primary key of the originating entity.
     * @param  string           $idempotencyKey  Unique key for duplicate detection.
     * @param  array<string,mixed> $metadata     Extra context passed to calculators and strategies.
     *                                           Keys: 'store_item_id', 'season_id', 'referrer_id', etc.
     * @param  int|null         $overrideAmount  When set, bypasses Rules Engine amount lookup.
     *                                           Admin grants may set explicit coin/XP amounts.
     * @param  bool             $dryRun          If true, pipeline runs but no DB writes occur.
     */
    public function __construct(
        public readonly int         $userId,
        public readonly RewardType  $rewardType,
        public readonly RewardSource $source,
        public readonly string      $sourceId,
        public readonly string      $idempotencyKey,
        public readonly array       $metadata       = [],
        public readonly ?int        $overrideAmount = null,
        public readonly bool        $dryRun         = false,
    ) {}

    /**
     * Convenience factory — derives idempotency key from parts.
     */
    public static function make(
        int          $userId,
        RewardType   $rewardType,
        RewardSource $source,
        string       $sourceId,
        array        $metadata       = [],
        ?int         $overrideAmount = null,
        bool         $dryRun         = false,
    ): self {
        $idempotencyKey = implode(':', [
            $source->value,
            $rewardType->value,
            $sourceId,
            $userId,
        ]);

        return new self(
            userId:          $userId,
            rewardType:      $rewardType,
            source:          $source,
            sourceId:        $sourceId,
            idempotencyKey:  $idempotencyKey,
            metadata:        $metadata,
            overrideAmount:  $overrideAmount,
            dryRun:          $dryRun,
        );
    }

    /**
     * Return a copy with dryRun=true (useful for simulating reward amounts in UI).
     */
    public function asDryRun(): self
    {
        return new self(
            userId:          $this->userId,
            rewardType:      $this->rewardType,
            source:          $this->source,
            sourceId:        $this->sourceId,
            idempotencyKey:  $this->idempotencyKey,
            metadata:        $this->metadata,
            overrideAmount:  $this->overrideAmount,
            dryRun:          true,
        );
    }

    /**
     * Retrieve a typed metadata value, or return the provided default.
     */
    public function meta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }
}
