<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Paper Trading Tycoon — Base Service
 *
 * Parent class for all application service classes. Provides:
 *   - transactional() helper for atomic operations with automatic rollback.
 *   - Structured logging helpers via LogHelper.
 *
 * Services contain business logic orchestration — they coordinate
 * between repositories, action classes, and domain event publishing.
 * Services must not contain raw Eloquent queries (use repositories).
 */
abstract class BaseService
{
    /**
     * Executes the given callable inside a database transaction.
     * Rolls back and re-throws on any exception.
     *
     * Use for all operations that modify multiple tables atomically
     * (e.g. debiting cash AND inserting a holdings ledger row).
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     *
     * @throws Throwable
     */
    protected function transactional(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Returns the short class name for structured log context.
     */
    protected function serviceContext(): string
    {
        return class_basename(static::class);
    }
}
