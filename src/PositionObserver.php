<?php

namespace Nevadskiy\Position;

use Illuminate\Database\Eloquent\Model;

class PositionObserver
{
    /**
     * Handle the "saving" event for the model.
     *
     * @param Model|HasPosition $model
     */
    public function saving(Model $model): void
    {
        $this->assignPositionIfMissing($model);

        $position = $model->getPosition();

        if ($position < $model->getStartPosition()) {
            $model->setPosition(max($this->count($model) + $position, $model->getStartPosition()));
        }

        // @todo cover with tests when position = -2
        // Do not shift positions of other models when models is created at the end of the sequence.
        if (($position === ($model->getStartPosition() - 1)) && ! $model->exists) {
            $model->syncOriginalAttributes($model->getPositionColumn());
        }
    }

    /**
     * Handle the "created" event for the model.
     *
     * @param Model|HasPosition $model
     */
    public function created(Model $model): void
    {
        if ($model->isMoving() && $model::shouldShiftPositions()) {
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
        if ($model->isMoving() && $model::shouldShiftPositions()) {
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
        if ($model::shouldShiftPositions()) {
            $model->newPositionQuery()->shiftToStart($model->getPosition());
        }
    }

    /**
     * Assign the position value to the model if it is missing.
     *
     * @param Model|HasPosition $model
     */
    protected function assignPositionIfMissing(Model $model): void
    {
        if (is_null($model->getAttribute($model->getPositionColumn()))) {
            $model->setPosition($this->getNextPosition($model));
        }
    }

    /**
     * Get the next position for the model.
     *
     * @param Model|HasPosition $model
     */
    protected function getNextPosition(Model $model): int
    {
        if ($model::positionLocker()) {
            return $model::positionLocker()($model);
        }

        return $model->getNextPosition();
    }

    /**
     * Get the models count in the sequence.
     *
     * @param Model|HasPosition $model
     */
    protected function count(Model $model): int
    {
        return $model->newPositionQuery()->count() + ($model->exists ? 0 : 1);
    }
}
