<?php

namespace Nevadskiy\Position;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PositioningScope implements Scope
{
    /**
     * Extend the query builder with the needed functions.
     */
    public function extend(Builder $query): void
    {
        $query->macro('wherePositionBetween', [$this, 'wherePositionBetween']);
        $query->macro('shiftToStart', [$this, 'shiftToStart']);
        $query->macro('shiftToEnd', [$this, 'shiftToEnd']);
        $query->macro('arrangeByKeys', [$this, 'arrangeByKeys']);
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Model|HasPosition $model
     */
    public function apply(Builder $query, Model $model): void
    {
        if ($model->alwaysOrderByPosition()) {
            $query->orderByPosition();
        }
    }

    /**
     * Select all models that are between the given positions.
     */
    public function wherePositionBetween(Builder $query, int $startPosition = null, int $endPosition = null): Builder
    {
        $query->when($startPosition !== null, static function (Builder $query) use ($startPosition) {
            $query->where($query->getModel()->getPositionColumn(), '>=', $startPosition);
        });

        $query->when($endPosition !== null, static function (Builder $query) use ($endPosition) {
            $query->where($query->getModel()->getPositionColumn(), '<=', $endPosition);
        });

        return $query;
    }

    /**
     * Shift all models that are between the given positions to the beginning of the sequence.
     */
    public function shiftToStart(Builder $query, int $fromPosition = null, int $toPosition = null, int $amount = 1): int
    {
        return $query->wherePositionBetween($fromPosition, $toPosition)
            ->decrement($query->getModel()->getPositionColumn(), $amount);
    }

    /**
     * Shift all models that are between the given positions to the end of the sequence.
     */
    public function shiftToEnd(Builder $query, int $fromPosition, int $toPosition = null, int $amount = 1): int
    {
        return $query->wherePositionBetween($fromPosition, $toPosition)
            ->increment($query->getModel()->getPositionColumn(), $amount);
    }

    /**
     * Arrange the models according to the given keys.
     */
    public function arrangeByKeys(Builder $query, array $keys, int $startPosition = 0): void
    {
        foreach ($keys as $position => $key) {
            (clone $query)->whereKey($key)
                ->update([
                    $query->getModel()->getPositionColumn() => $startPosition + $position,
                ]);
        }
    }
}
