<?php

namespace Nevadskiy\Position;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait HasPosition
{
    /**
     * Indicates if the model should shift position of other models in the sequence.
     *
     * @var bool
     */
    protected static $shiftPosition = true;

    /**
     * Boot the trait.
     */
    public static function bootHasPosition(): void
    {
        static::addGlobalScope(new PositioningScope());

        static::observe(new PositionObserver());
    }

    /**
     * Initialize the trait.
     */
    public function initializeHasPosition(): void
    {
        $this->mergeCasts([
            $this->getPositionColumn() => 'int',
        ]);
    }

    /**
     * Get the name of the "position" column.
     */
    public function getPositionColumn(): string
    {
        return 'position';
    }

    /**
     * Get the next position in the sequence for the model.
     */
    public function getNextPosition(): int
    {
        return -1;
    }

    /**
     * Determine if the order by position should be applied always.
     */
    public function alwaysOrderByPosition(): bool
    {
        return false;
    }

    /**
     * Execute a callback without shifting position of models.
     */
    public static function withoutShiftingPosition(callable $callback)
    {
        $shifting = static::$shiftPosition;

        static::$shiftPosition = false;

        $result = $callback();

        static::$shiftPosition = $shifting;

        return $result;
    }

    /**
     * Determine if the model should shift position of other models in the sequence.
     */
    public static function shouldShiftPosition(): bool
    {
        return static::$shiftPosition;
    }

    /**
     * Get the position value of the model.
     */
    public function getPosition(): int
    {
        return $this->getAttribute($this->getPositionColumn());
    }

    /**
     * Set the position to the given value.
     */
    public function setPosition(int $position): Model
    {
        return $this->setAttribute($this->getPositionColumn(), $position);
    }

    /**
     * Scope a query to sort models by positions.
     */
    protected function scopeOrderByPosition(Builder $query): Builder
    {
        return $query->orderBy($this->getPositionColumn());
    }

    /**
     * Scope a query to sort models by reverse positions.
     */
    protected function scopeOrderByReversePosition(Builder $query): Builder
    {
        return $query->orderBy($this->getPositionColumn(), 'desc');
    }

    /**
     * Move the model to the new position.
     */
    public function move(int $newPosition): bool
    {
        $oldPosition = $this->getPosition();

        if ($oldPosition === $newPosition) {
            return false;
        }

        return $this->setPosition($newPosition)->save();
    }

    /**
     * Determine if the model is currently moving to a new position.
     */
    public function isMoving(): bool
    {
        return $this->isDirty($this->getPositionColumn());
    }

    /**
     * Swap the model position with another model.
     */
    public function swap(self $that): void
    {
        static::withoutShiftingPosition(function () use ($that) {
            $thisPosition = $this->getPosition();
            $thatPosition = $that->getPosition();

            $this->setPosition($thatPosition);
            $that->setPosition($thisPosition);

            $this->save();
            $that->save();
        });
    }

    /**
     * Get a new position query.
     */
    public function newPositionQuery(): Builder
    {
        return $this->newQuery();
    }
}
