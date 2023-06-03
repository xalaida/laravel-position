<?php

namespace Nevadskiy\Position;

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
            $model->newPositionQuery()->whereKeyNot($model->getKey())->shiftToEnd($model->getPosition());
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
                $model->newPositionQuery()->whereKeyNot($model->getKey())->shiftToEnd($newPosition, $oldPosition);
            } elseif ($newPosition > $oldPosition) {
                $model->newPositionQuery()->whereKeyNot($model->getKey())->shiftToStart($oldPosition, $newPosition);
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
            $model->newPositionQuery()->shiftToStart($model->getPosition());
        }
    }
}
