<?php

namespace Nevadskiy\Position\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Nevadskiy\Position\HasPosition;

class PositioningScope implements Scope
{
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
     * Extend the query builder with the needed functions.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('arrangeByKeys', [$this, 'arrangeByKeys']);
    }

    /**
     * Arrange the models according to the given ordered keys.
     */
    public function arrangeByKeys(Builder $builder, array $keys, int $startPosition = null): void
    {
        $startPosition = is_null($startPosition) ? $builder->getModel()->getInitPosition() : $startPosition;

        foreach ($keys as $position => $key) {
            (clone $builder)->whereKey($key)
                ->update([
                    $builder->getModel()->getPositionColumn() => $startPosition + $position
                ]);
        }
    }
}