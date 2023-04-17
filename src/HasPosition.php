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

        static::creating(static function (self $model) {
            $model->assignPositionIfMissing();
        });

        static::created(static function (self $model) {
            if (static::shouldShiftPosition() && $model->isMoving()) {
                $model->newPositionQuery()->whereKeyNot($model->getKey())->shiftToEnd($model->getPosition());
            }
        });

        static::updated(static function (self $model) {
            if (static::shouldShiftPosition() && $model->isMoving()) {
                [$newPosition, $oldPosition] = [$model->getPosition(), $model->getOriginal($model->getPositionColumn())];

                if ($newPosition < $oldPosition) {
                    $model->newPositionQuery()->whereKeyNot($model->getKey())->shiftToEnd($newPosition, $oldPosition);
                } elseif ($newPosition > $oldPosition) {
                    $model->newPositionQuery()->whereKeyNot($model->getKey())->shiftToStart($oldPosition, $newPosition);
                }
            }
        });

        static::deleted(static function (self $model) {
            if (static::shouldShiftPosition()) {
                $model->newPositionQuery()->shiftToStart($model->getPosition());
            }
        });
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
        if ($position < 0) {
            $position = ($this->getMaxPosition() + 1) + $position + ($this->exists ? 0 : 1);
        }

        // @todo throw overflow exception when negative value is too big. @see https://github.com/es-shims/Array.prototype.at/blob/main/implementation.js#L18.

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
    protected function newPositionQuery(): Builder
    {
        return $this->newQuery();
    }

    /**
     * Assign the next position value to the model if it is missing.
     */
    protected function assignPositionIfMissing(): void
    {
        if (is_null($this->getAttribute($this->getPositionColumn()))) {
            $nextPosition = $this->getNextPosition();

            $this->setPosition($nextPosition);

            // Sync original attribute to make it not dirty to do not shift the positions of other models.
            if ($nextPosition < 0) {
                $this->syncOriginalAttribute($this->getPositionColumn());
            }
        }
    }

    /**
     * Get the max position value in the model sequence.
     */
    protected function getMaxPosition(): int
    {
        return $this->newPositionQuery()->max($this->getPositionColumn()) ?? -1;
    }
}
