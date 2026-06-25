<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Paper Trading Tycoon — Base Repository Contract
 *
 * Defines the common read/write interface implemented by all Eloquent
 * repositories. Repositories abstract raw Eloquent queries from service
 * classes, keeping the service layer testable against this contract.
 *
 * Complex queries specific to a module (e.g. leaderboard ranking)
 * are defined in module-specific repository contracts.
 *
 * @template TModel of Model
 */
interface BaseRepositoryContract
{
    /**
     * Find a model by its primary key or throw ModelNotFoundException.
     *
     * @return TModel
     */
    public function findOrFail(int|string $id): Model;

    /**
     * Find a model by its primary key or return null.
     *
     * @return TModel|null
     */
    public function find(int|string $id): ?Model;

    /**
     * Create a new model instance from the given attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function create(array $attributes): Model;

    /**
     * Update an existing model by ID with the given attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function update(int|string $id, array $attributes): Model;

    /**
     * Soft-delete or hard-delete a model by ID.
     */
    public function delete(int|string $id): bool;
}
