<?php

namespace Nevadskiy\Position;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Position\Scopes\PositioningScope;

/**
 * @mixin Model
 */
trait HasPosition
{
    /**
     * Boot the position trait.
     */
    public static function bootHasPosition(): void
    {
        static::addGlobalScope(new PositioningScope());

        static::creating(static function (self $model) {
            $model->assignPositionIfMissing();
        });

        static::deleted(static function (self $model) {
            $model->newPositionQuery()->shiftToStart($model->getPosition());
        });
    }

    /**
     * Get the name of the "position" column.
     */
    public function getPositionColumn(): string
    {
        return 'position';
    }

    /**
     * Get the initial position value.
     */
    public function getInitPosition(): int
    {
        return 0;
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
    public function getPosition(): ?int
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
     * Scope a query to sort models by inverse positions.
     */
    public function scopeOrderByInversePosition(Builder $query): Builder
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

        if ($newPosition < $oldPosition) {
            $this->newPositionQuery()->shiftToEnd($newPosition, $oldPosition);
        } elseif ($newPosition > $oldPosition) {
            $this->newPositionQuery()->shiftToStart($oldPosition, $newPosition);
        }

        $this->setPosition($newPosition);

        return $this->save();
    }

    /**
     * Swap the model position with another model.
     */
    public function swap(self $that): void
    {
        $thisPosition = $this->getPosition();
        $thatPosition = $that->getPosition();

        $this->setPosition($thatPosition);
        $that->setPosition($thisPosition);

        $this->save();
        $that->save();
    }

    /**
     * Get a new position query.
     */
    protected function newPositionQuery(): Builder
    {
        return $this->newQuery();
    }

    /**
     * Assign the next position value to the model if it is missing.
     */
    protected function assignPositionIfMissing(): void
    {
        if (null === $this->getPosition()) {
            $this->assignNextPosition();
        }
    }

    /**
     * Assign the next position value to the model.
     */
    protected function assignNextPosition(): Model
    {
        return $this->setPosition($this->getNextPositionInSequence());
    }

    /**
     * Determine the next position value in the model sequence.
     */
    protected function getNextPositionInSequence(): int
    {
        $maxPosition = $this->getMaxPositionInSequence();

        if (null === $maxPosition) {
            return $this->getInitPosition();
        }

        return $maxPosition + 1;
    }

    /**
     * Get the max position value in the model sequence.
     */
    protected function getMaxPositionInSequence(): ?int
    {
        return $this->newPositionQuery()->max($this->getPositionColumn());
    }
}
