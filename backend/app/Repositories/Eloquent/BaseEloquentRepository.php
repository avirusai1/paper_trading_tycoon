<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Paper Trading Tycoon — Base Eloquent Repository
 *
 * Provides the standard CRUD implementation for all Eloquent repositories.
 * Module repositories extend this class and override only what they need.
 *
 * @template TModel of Model
 * @implements BaseRepositoryContract<TModel>
 */
abstract class BaseEloquentRepository implements BaseRepositoryContract
{
    /**
     * The Eloquent model class managed by this repository.
     *
     * @var class-string<TModel>
     */
    abstract protected function modelClass(): string;

    /**
     * @return TModel
     */
    public function findOrFail(int|string $id): Model
    {
        /** @var TModel */
        return $this->modelClass()::findOrFail($id);
    }

    /**
     * @return TModel|null
     */
    public function find(int|string $id): ?Model
    {
        /** @var TModel|null */
        return $this->modelClass()::find($id);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function create(array $attributes): Model
    {
        /** @var TModel */
        return $this->modelClass()::create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return TModel
     *
     * @throws ModelNotFoundException
     */
    public function update(int|string $id, array $attributes): Model
    {
        $model = $this->findOrFail($id);
        $model->update($attributes);

        return $model->fresh() ?? $model;
    }

    /**
     * @throws ModelNotFoundException
     */
    public function delete(int|string $id): bool
    {
        $model = $this->findOrFail($id);

        return (bool) $model->delete();
    }
}
