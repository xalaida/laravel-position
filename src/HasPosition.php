<?php

namespace Nevadskiy\Position;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait HasPosition
{
    use PositionLocker;

    /**
     * Indicates if the model should shift position of other models in the sequence.
     *
     * @var bool
     */
    protected static $shiftPosition = true;

    /**
     * Indicates if the model was positioned at the end of the sequence during the current request lifecycle.
     *
     * @var bool
     */
    public $terminal = false;

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
     * Get the starting position for the model.
     */
    public function getStartPosition(): int
    {
        return 0;
    }

    /**
     * Get the next position in the sequence for the model.
     */
    public function getNextPosition(): int
    {
        return $this->getStartPosition() - 1;
    }

    /**
     * Determine if the order by position should be applied always.
     */
    public function alwaysOrderByPosition(): bool
    {
        return false;
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
    public function scopeOrderByPosition(Builder $query): Builder
    {
        return $query->orderBy($this->getPositionColumn());
    }

    /**
     * Scope a query to sort models by reverse positions.
     */
    public function scopeOrderByReversePosition(Builder $query): Builder
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
     * Get attributes for grouping positions.
     */
    public function groupPositionBy(): array
    {
        return [];
    }

    /**
     * Get a new position query.
     */
    public function newPositionQuery(): Builder
    {
        $query = $this->newQuery();

        foreach ($this->groupPositionBy() as $attribute) {
            $query->where([$attribute => $this->getAttribute($attribute)]);
        }

        return $query;
    }

    /**
     * Get a new original position query.
     */
    public function newOriginalPositionQuery(): Builder
    {
        $query = $this->newQuery();

        foreach ($this->groupPositionBy() as $attribute) {
            $query->where([$attribute => $this->getOriginal($attribute)]);
        }

        return $query;
    }

    /**
     * Execute a callback without shifting positions of models.
     */
    public static function withoutShiftingPosition(callable $callback)
    {
        $shiftPosition = static::$shiftPosition;

        static::$shiftPosition = false;

        $result = $callback();

        static::$shiftPosition = $shiftPosition;

        return $result;
    }

    /**
     * Determine if the model should shift positions of other models in the sequence.
     */
    public static function shouldShiftPosition(): bool
    {
        return static::$shiftPosition && is_null(static::$positionLocker);
    }
}
