<?php

namespace Nevadskiy\Position\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Nevadskiy\Position\HasPosition;

class PositioningScope implements Scope
{
    /**
     * Extend the query builder with the needed functions.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('arrangeByKeys', [$this, 'arrangeByKeys']);
        $builder->macro('shiftToStart', [$this, 'shiftToStart']);
        $builder->macro('shiftToEnd', [$this, 'shiftToEnd']);
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param HasPosition|Model $model
     */
    public function apply(Builder $query, Model $model): void
    {
        if ($model->alwaysOrderByPosition()) {
            $query->orderByPosition();
        }
    }

    /**
     * Arrange the models according to the given ordered keys.
     */
    public function arrangeByKeys(Builder $builder, array $keys, int $startPosition = null): void
    {
        $startPosition = $startPosition ?? $builder->getModel()->getInitPosition();

        foreach ($keys as $position => $key) {
            (clone $builder)->whereKey($key)
                ->update([
                    $builder->getModel()->getPositionColumn() => $startPosition + $position,
                ]);
        }
    }

    /**
     * Shift all models that are between the given positions to the beginning of the sequence.
     */
    public function shiftToStart(Builder $builder, int $startPosition, int $stopPosition = null): int
    {
        return $builder->where($builder->getModel()->getPositionColumn(), '>=', $startPosition)
            ->when($stopPosition, static function (Builder $builder) use ($stopPosition) {
                $builder->where($builder->getModel()->getPositionColumn(), '<=', $stopPosition);
            })
            ->decrement($builder->getModel()->getPositionColumn());
    }

    /**
     * Shift all models that are between the given positions to the end of the sequence.
     */
    public function shiftToEnd(Builder $builder, int $startPosition, int $stopPosition = null): int
    {
        return $builder->where($builder->getModel()->getPositionColumn(), '>=', $startPosition)
            ->when($stopPosition, static function (Builder $builder) use ($stopPosition) {
                $builder->where($builder->getModel()->getPositionColumn(), '<=', $stopPosition);
            })
            ->increment($builder->getModel()->getPositionColumn());
    }
}
