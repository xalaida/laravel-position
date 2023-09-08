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
        if ($this->shouldSetNextPosition($model)) {
            $model->setPosition($this->getNextPosition($model));
        }

        $position = $model->getPosition();

        if ($position < $model->getStartPosition()) {
            $model->setPosition(max($this->count($model) + $position, $model->getStartPosition()));
        }

        if ($this->isSavingAsLatest($model, $position)) {
            // Prevent shifting the position of other models, avoiding the need for extra database query.
            $model->syncOriginalAttributes($model->getPositionColumn());
        }
    }

    /**
     * Determine whether it should set position to the model.
     *
     * @param Model|HasPosition $model
     */
    protected function shouldSetNextPosition(Model $model): bool
    {
        if ($model->getAttribute($model->getPositionColumn()) === null) {
            return true;
        }

        $groupAttributes = $model->groupPositionBy();

        return $model->exists && $groupAttributes && $model->isDirty($groupAttributes);
    }

    /**
     * Handle the "created" event for the model.
     *
     * @param Model|HasPosition $model
     */
    public function created(Model $model): void
    {
        if ($model->isMoving() && $model::shouldShiftPosition()) {
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
        $this->syncPositionGroup($model);
        $this->syncOriginalPositionGroup($model);
    }

    /**
     * Sync the position group.
     *
     * @param Model|HasPosition $model
     */
    protected function syncPositionGroup(Model $model): void
    {
        if ($model->isMoving() && $model::shouldShiftPosition()) {
            [$newPosition, $oldPosition] = [$model->getPosition(), $model->getOriginal($model->getPositionColumn())];

            if ($newPosition < $oldPosition) {
                $model->newPositionQuery()->whereKeyNot($model->getKey())->shiftToEnd($newPosition, $oldPosition);
            } elseif ($newPosition > $oldPosition) {
                $model->newPositionQuery()->whereKeyNot($model->getKey())->shiftToStart($oldPosition, $newPosition);
            }
        }
    }

    /**
     * Sync the original position group.
     *
     * @param Model|HasPosition $model
     */
    protected function syncOriginalPositionGroup(Model $model): void
    {
        $groupAttributes = $model->groupPositionBy();

        if ($groupAttributes && $model->wasChanged($groupAttributes)) {
            $model->newOriginalPositionQuery()
                ->whereKeyNot($model->getKey())
                ->shiftToStart($model->getOriginal($model->getPositionColumn()));
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
     * Determine if the model is being saved at the end of the sequence.
     *
     * @param Model|HasPosition $model
     */
    protected function isSavingAsLatest(Model $model, int $position): bool
    {
        if ($model->exists) {
            return false;
        }

        return $position === ($model->getStartPosition() - 1);
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
