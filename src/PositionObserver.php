<?php

namespace Nevadskiy\Position;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PositionObserver
{
    /**
     * Handle the "creating" event for the model.
     *
     * @param Model|HasPosition $model
     */
    public function creating(Model $model): void
    {
        $model->assignPositionIfMissing();
    }

    /**
     * Handle the "created" event for the model.
     *
     * @param Model|HasPosition $model
     */
    public function created(Model $model): void
    {
        if ($model::shouldShiftPosition() && $model->isMoving()) {
            $this->others($model)->shiftToEnd($model->getPosition());
        }
    }

    /**
     * Handle the "updated" event for the model.
     *
     * @param Model|HasPosition $model
     */
    public function updated(Model $model): void
    {
        if ($model::shouldShiftPosition() && $model->isMoving()) {
            [$newPosition, $oldPosition] = [$model->getPosition(), $model->getOriginal($model->getPositionColumn())];

            if ($newPosition < $oldPosition) {
                $this->others($model)->shiftToEnd($newPosition, $oldPosition);
            } elseif ($newPosition > $oldPosition) {
                $this->others($model)->shiftToStart($oldPosition, $newPosition);
            }
        }
    }

    /**
     * Handle the "deleted" event for the model.
     *
     * @param Model|HasPosition $model
     */
    public function deleted(Model $model): void
    {
        if ($model::shouldShiftPosition()) {
            $this->others($model)->shiftToStart($model->getPosition());
        }
    }

    /**
     * Get other models in the sequence.
     *
     * @param Model|HasPosition $model
     */
    protected function others(Model $model): Builder
    {
        $query = $model->newPositionQuery();

        if ($model->exists) {
            $query->whereKeyNot($model->getKey());
        }

        return $query;
    }
}
