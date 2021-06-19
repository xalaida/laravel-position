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
}