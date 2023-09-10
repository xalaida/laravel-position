<?php

namespace Nevadskiy\Position;

use Illuminate\Database\Eloquent\Model;

class PositionObserver
{
    /**
     * The list of models classes that should lock positions.
     *
     * @var array
     */
    protected static $lockedFor = [];

    /**
     * Enable the position lock for the given model.
     */
    public static function lockFor(string $model): void
    {
        static::$lockedFor[$model] = true;
    }

    /**
     * Disable the position lock for the given model.
     */
    public static function unlockFor(string $model): void
    {
        unset(static::$lockedFor[$model]);
    }

    /**
     * Determine whether the position is locked for the given model.
     *
     * @param Model|string $model
     */
    public static function isLockedFor($model): bool
    {
        $model = is_object($model) ? get_class($model) : $model;

        return isset(static::$lockedFor[$model]);
    }

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
            $model->setPosition($model->getNextPosition());
        }
    }

    /**
     * Determine if a position should be set for the model.
     *
     * @param Model|HasPosition $model
     */
    protected function shouldSetPosition(Model $model): bool
    {
        $positionColumn = $model->getPositionColumn();

        if ($model->isDirty($positionColumn)) {
            return false;
        }

        if ($model->getAttribute($positionColumn) === null) {
            return true;
        }

        return $this->isGroupChanging($model);
    }

    /**
     * Determine if the position group is changing for the model.
     *
     * @param Model|HasPosition $model
     */
    protected function isGroupChanging(Model $model): bool
    {
        $groupPositionColumns = $model->groupPositionBy();

        if (! $groupPositionColumns) {
            return false;
        }

        return $model->isDirty($groupPositionColumns);
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
     * Normalize the position value for the model.
     *
     * @param Model|HasPosition $model
     */
    protected function normalizePosition(Model $model): void
    {
        if ($model->getPosition() >= $model->getStartPosition()) {
            return;
        }

        $position = $model->getPosition() + $model->newPositionQuery()->count();

        if (! $model->exists || $this->isGroupChanging($model)) {
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
        if (static::isLockedFor($model)) {
            return;
        }

        $this->handleAddToGroup($model);
    }

    /**
     * Handle the model adding to the position group.
     *
     * @param Model|HasPosition $model
     */
    protected function handleAddToGroup(Model $model): void
    {
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
        if (static::isLockedFor($model)) {
            return;
        }

        if ($this->wasGroupChanged($model)) {
            $this->handleGroupChange($model);
        } elseif ($this->wasPositionChanged($model)) {
            $this->handlePositionChange($model);
        }
    }

    /**
     * Determine if the position group was changed for the model.
     *
     * @param Model|HasPosition $model
     */
    protected function wasGroupChanged(Model $model): bool
    {
        $groupPositionColumns = $model->groupPositionBy();

        if (! $groupPositionColumns) {
            return false;
        }

        return $model->wasChanged($groupPositionColumns);
    }

    /**
     * Determine if the position was changed for the model.
     *
     * @param Model|HasPosition $model
     */
    protected function wasPositionChanged(Model $model): bool
    {
        return $model->wasChanged($model->getPositionColumn());
    }

    /**
     * Handle the position group change for the model.
     *
     * @param Model|HasPosition $model
     */
    protected function handleGroupChange(Model $model): void
    {
        $this->handleRemoveFromGroup($model);
        $this->handleAddToGroup($model);
    }

    /**
     * Handle the position change for the model.
     *
     * @param Model|HasPosition $model
     */
    protected function handlePositionChange(Model $model): void
    {
        $positionColumn = $model->getPositionColumn();
        $currentPosition = $model->getAttribute($positionColumn);
        $originalPosition = $model->getOriginal($positionColumn);

        if ($currentPosition < $originalPosition) {
            $model->newPositionQuery()
                ->whereKeyNot($model->getKey())
                ->shiftToEnd($currentPosition, $originalPosition);
        } elseif ($currentPosition > $originalPosition) {
            $model->newPositionQuery()
                ->whereKeyNot($model->getKey())
                ->shiftToStart($originalPosition, $currentPosition);
        }
    }

    /**
     * Handle the "deleted" event for the model.
     *
     * @param Model|HasPosition $model
     */
    public function deleted(Model $model): void
    {
        if (static::isLockedFor($model)) {
            return;
        }

        $this->handleRemoveFromGroup($model);
    }

    /**
     * Handle the model removing for the position group.
     *
     * @param Model|HasPosition $model
     */
    protected function handleRemoveFromGroup(Model $model): void
    {
        $model->newOriginalPositionQuery()
            ->shiftToStart(
                $model->getOriginal($model->getPositionColumn())
            );
    }
}
