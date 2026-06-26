<?php

declare(strict_types=1);

namespace App\GameEngine\Actions;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\Contracts\GameRuleProviderContract;
use App\GameEngine\DTOs\XPResult;
use App\GameEngine\Enums\XPSource;
use App\GameEngine\Exceptions\XPException;
use App\GameEngine\Support\DailyCapTracker;
use App\GameEngine\Support\XPMultiplierCalculator;
use App\Models\Level;
use App\Models\UserLevel;
use App\Models\XpLog;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Atomically grants XP to a user, enforcing daily caps and multipliers.
 *
 * Responsibilities:
 * 1. Calculate effective XP after multipliers (from XPMultiplierCalculator).
 * 2. Enforce daily cap (from DailyCapTracker + Rules Engine).
 * 3. Persist XpLog entry (append-only, idempotent via UNIQUE index).
 * 4. Update UserLevel.current_xp and detect level-up.
 * 5. Return XPResult describing the outcome.
 *
 * Does NOT dispatch domain events — the calling processor handles that.
 */
final class GrantXPAction
{
    public function __construct(
        private readonly GameRuleProviderContract $rules,
        private readonly XPMultiplierCalculator $multiplierCalc,
        private readonly DailyCapTracker $capTracker,
    ) {}

    /**
     * @throws XPException
     */
    public function execute(
        GameContext $context,
        XPSource $source,
        string $sourceId,
        ?int $overrideAmount = null,
    ): XPResult {
        // 1. Determine base XP amount
        $baseAmount = $overrideAmount ?? $this->rules->getInt($source->ruleKey(), 0);
        if ($baseAmount <= 0) {
            // Source has no XP value in rules — return zero-grant result
            return $this->zeroResult($context, $source, $sourceId);
        }

        // 2. Apply multiplier
        $multiplier = $this->multiplierCalc->calculate($context);
        $effectiveXP = (int) floor($baseAmount * $multiplier);

        // 3. Enforce daily cap
        if ($source->hasDailyCap()) {
            $capKey = $source->dailyCapRuleKey();
            $cap = $capKey !== null ? $this->rules->getInt($capKey) : PHP_INT_MAX;
            $today = $this->capTracker->getDailyTotal($context->userId(), $source);
            $remaining = $cap - $today;

            if ($remaining <= 0) {
                return $this->zeroResult($context, $source, $sourceId, wasCapApplied: true);
            }

            $effectiveXP = min($effectiveXP, $remaining);
        }

        // 4. Persist atomically
        return DB::transaction(function () use ($context, $source, $sourceId, $effectiveXP): XPResult {
            $userLevel = $context->userLevel;
            $xpBefore = $userLevel->current_xp;
            $levelBefore = $userLevel->current_level;

            // Upsert UserLevel XP (lock for update to prevent concurrent race)
            UserLevel::where('user_id', $context->userId())
                ->lockForUpdate()
                ->update(['current_xp' => DB::raw("current_xp + {$effectiveXP}")]);

            $updatedLevel = UserLevel::where('user_id', $context->userId())->first();
            $xpAfter = $updatedLevel->current_xp;

            // Determine new level
            $newLevel = $this->calculateLevel($xpAfter);
            $levelChanged = $newLevel !== $levelBefore;

            if ($levelChanged) {
                UserLevel::where('user_id', $context->userId())
                    ->update([
                        'current_level' => $newLevel,
                        'xp_in_current_level' => $this->xpInCurrentLevel($xpAfter, $newLevel),
                        'level_achieved_at' => now(),
                    ]);
            }

            // Append-only XP log (DB UNIQUE prevents double-grant on retry)
            try {
                XpLog::create([
                    'user_id' => $context->userId(),
                    'amount' => $effectiveXP,
                    'source' => $source->value,
                    'source_id' => $sourceId,
                    'xp_before' => $xpBefore,
                    'xp_after' => $xpAfter,
                    'level_before' => $levelBefore,
                    'level_after' => $newLevel,
                ]);
            } catch (UniqueConstraintViolationException) {
                // Already granted — idempotent no-op, reconstruct result from existing log
                $existing = XpLog::where('user_id', $context->userId())
                    ->where('source', $source->value)
                    ->where('source_id', $sourceId)
                    ->firstOrFail();

                return new XPResult(
                    userId: $context->userId(),
                    amountGranted: $existing->amount,
                    xpBefore: $existing->xp_before,
                    xpAfter: $existing->xp_after,
                    levelBefore: $existing->level_before,
                    levelAfter: $existing->level_after,
                    didLevelUp: $existing->level_after > $existing->level_before,
                    source: $source->value,
                    sourceId: $sourceId,
                    wasCapApplied: false,
                );
            }

            // Update daily cap tracker cache
            if ($source->hasDailyCap()) {
                $this->capTracker->increment($context->userId(), $source, $effectiveXP);
            }

            return new XPResult(
                userId: $context->userId(),
                amountGranted: $effectiveXP,
                xpBefore: $xpBefore,
                xpAfter: $xpAfter,
                levelBefore: $levelBefore,
                levelAfter: $newLevel,
                didLevelUp: $levelChanged,
                source: $source->value,
                sourceId: $sourceId,
                wasCapApplied: false,
            );
        });
    }

    /**
     * Look up which level corresponds to total XP by querying the levels table.
     * Falls back to level 1 if no matching level is found (shouldn't happen
     * if LevelsSeeder has run).
     */
    private function calculateLevel(int $totalXP): int
    {
        $level = Level::where('xp_required', '<=', $totalXP)
            ->orderByDesc('level_number')
            ->value('level_number');

        return $level ?? 1;
    }

    private function xpInCurrentLevel(int $totalXP, int $level): int
    {
        $levelFloor = (int) Level::where('level_number', $level)->value('xp_required');

        return max(0, $totalXP - $levelFloor);
    }

    private function zeroResult(
        GameContext $context,
        XPSource $source,
        string $sourceId,
        bool $wasCapApplied = false,
    ): XPResult {
        return new XPResult(
            userId: $context->userId(),
            amountGranted: 0,
            xpBefore: $context->currentXP(),
            xpAfter: $context->currentXP(),
            levelBefore: $context->currentLevel(),
            levelAfter: $context->currentLevel(),
            didLevelUp: false,
            source: $source->value,
            sourceId: $sourceId,
            wasCapApplied: $wasCapApplied,
        );
    }
}
