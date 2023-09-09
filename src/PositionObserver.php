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
        $this->assignPosition($model);
        $this->markAsTerminalPosition($model);
        $this->normalizePosition($model);
    }

    /**
     * Assign a position to the model.
     *
     * @param Model|HasPosition $model
     */
    protected function assignPosition(Model $model): void
    {
        if ($this->shouldSetPosition($model)) {
            $model->setPosition($this->getNextPosition($model));
        }
    }

    /**
     * Determine if a position should be set for the model.
     *
     * @param Model|HasPosition $model
     */
    protected function shouldSetPosition(Model $model): bool
    {
        if ($model->isDirty($model->getPositionColumn())) {
            return false;
        }

        if ($model->getAttribute($model->getPositionColumn()) === null) {
            return true;
        }

        return $this->isChangingPositionGroup($model);
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
     * Mark the model as terminal if it is positioned at the end of the sequence.
     *
     * @param Model|HasPosition $model
     */
    protected function markAsTerminalPosition(Model $model): void
    {
        $model->terminal = $model->getPosition() === ($model->getStartPosition() - 1);
    }

    /**
     * @param Model|HasPosition $model
     */
    protected function normalizePosition(Model $model): void
    {
        if ($model->getPosition() >= $model->getStartPosition()) {
            return;
        }

        $position = $model->getPosition() + $model->newPositionQuery()->count();

        if (! $model->exists || $this->isChangingPositionGroup($model)) {
            $position++;
        }

        $model->setPosition($position);
    }

    /**
     * Handle the "created" event for the model.
     *
     * @param Model|HasPosition $model
     */
    public function created(Model $model): void
    {
        if (! $model::shouldShiftPosition()) {
            return;
        }

        if (! $model->terminal) {
            $model->newPositionQuery()
                ->whereKeyNot($model->getKey())
                ->shiftToEnd($model->getPosition());
        }
    }

    /**
     * Handle the "updated" event for the model.
     *
     * @param Model|HasPosition $model
     */
    public function updated(Model $model): void
    {
        if (! $model::shouldShiftPosition()) {
            return;
        }

        if ($this->wasChangedPositionGroup($model)) {
            $model->newOriginalPositionQuery()
                ->whereKeyNot($model->getKey())
                ->shiftToStart($model->getOriginal($model->getPositionColumn()));

            if (! $model->terminal) {
                $model->newPositionQuery()
                    ->whereKeyNot($model->getKey())
                    ->shiftToEnd($model->getPosition());
            }
        } else if ($model->wasChanged($model->getPositionColumn())) {
            [$newPosition, $oldPosition] = [$model->getPosition(), $model->getOriginal($model->getPositionColumn())];

            if ($newPosition < $oldPosition) {
                $model->newPositionQuery()
                    ->whereKeyNot($model->getKey())
                    ->shiftToEnd($newPosition, $oldPosition);
            } elseif ($newPosition > $oldPosition) {
                $model->newPositionQuery()
                    ->whereKeyNot($model->getKey())
                    ->shiftToStart($oldPosition, $newPosition);
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
        if (! $model::shouldShiftPosition()) {
            return;
        }

        $model->newPositionQuery()->shiftToStart($model->getPosition());
    }

    /**
     * @param Model|HasPosition $model
     */
    protected function isChangingPositionGroup(Model $model): bool
    {
        return $model->groupPositionBy() && $model->isDirty($model->groupPositionBy());
    }

    /**
     * @param Model|HasPosition $model
     */
    protected function wasChangedPositionGroup(Model $model): bool
    {
        return $model->groupPositionBy() && $model->wasChanged($model->groupPositionBy());
    }
}
