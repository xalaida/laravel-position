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
        if ($model->getAttribute($model->getPositionColumn()) === null) {
            $model->setPosition($this->getNextPosition($model));
        }

        $position = $model->getPosition();

        if ($position < $model->getStartPosition()) {
            $count = $model->newPositionQuery()->count(); // @todo probably use max() instead of count.

            $position += $count;

            $groupAttributes = $model->groupPositionBy();

            if (! $model->exists || ($groupAttributes && $model->isDirty($groupAttributes))) {
                $position++;
            }

            $model->terminal = $position === $count;

            $position = max($position, $model->getStartPosition());

            $model->setPosition($position);
        }
    }

    /**
     * Handle the "updating" event for the model.
     *
     * @param Model|HasPosition $model
     */
    public function updating(Model $model): void
    {
        $groupAttributes = $model->groupPositionBy();

        if ($groupAttributes && $model->isDirty($groupAttributes) && ! $model->isDirty($model->getPositionColumn())) {
            $model->setPosition($this->getNextPosition($model));
        }

        $position = $model->getPosition();

        if ($position < $model->getStartPosition()) {
            $count = $model->newPositionQuery()->count(); // @todo probably use max() instead of count.

            $position += $count;

            if (! $model->exists || ($groupAttributes && $model->isDirty($groupAttributes))) {
                $position++;
            }

            if ($position === $count) {
                $model->terminal = true;
            }

            $position = max($position, $model->getStartPosition());

            $model->setPosition($position);
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
        if (! $model::shouldShiftPosition()) {
            return;
        }

        $groupAttributes = $model->groupPositionBy();

        if ($groupAttributes && $model->wasChanged($groupAttributes)) {
            $model->newOriginalPositionQuery()
                ->whereKeyNot($model->getKey())
                ->shiftToStart($model->getOriginal($model->getPositionColumn()));

            // @todo ensure is not terminal...
            $model->newPositionQuery()
                ->whereKeyNot($model->getKey())
                ->shiftToEnd($model->getPosition());
        } else if ($model->wasChanged($model->getPositionColumn())) {
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
        if (! $model::shouldShiftPosition()) {
            return;
        }

        $model->newPositionQuery()->shiftToStart($model->getPosition());
    }
}
