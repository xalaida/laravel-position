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
    protected $shiftPosition = false;

    /**
     * Boot the trait.
     */
    public static function bootHasPosition(): void
    {
        static::addGlobalScope(new PositioningScope());

        static::creating(static function (self $model) {
            if ($model->getPosition() === null) {
                $model->assignPosition();
            } else {
                $model->withShiftPosition();
            }
        });

        // @todo consider using "saved" event for this.
        static::created(static function (self $model) {
            if ($model->shouldShiftPosition()) {
                // @todo consider extracting into "others" method.
                $model->newPositionQuery()->whereKeyNot($model->getKey())->shiftToEnd($model->getPosition());
            }
        });

        static::updating(static function (self $model) {
            if ($model->isDirty($model->getPositionColumn())) {
                $model->shiftBeforeMove($model->getPosition(), $model->getOriginal($model->getPositionColumn()));
            }
        });

        static::deleted(static function (self $model) {
            $model->newPositionQuery()->shiftToStart($model->getPosition());
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
     * Get a value of the starting position.
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
     * Determine if the model should shift position of other models in the sequence.
     */
    public function shouldShiftPosition(): bool
    {
        return $this->shiftPosition;
    }

    public function withShiftPosition(bool $value = true): self
    {
        $this->shiftPosition = $value;

        return $this;
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

        return $this->setPosition($newPosition)->save();
    }

    /**
     * Swap the model position with another model.
     */
    public function swap(self $that): void
    {
        static::withoutEvents(function () use ($that) {
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

//    /**
//     * Assign the next position value to the model if it is missing.
//     */
//    protected function assignPositionIfMissing(): void
//    {
//        if (null === $this->getPosition()) {
//            $this->assignPosition();
//        }
//    }

    /**
     * Assign the next position value to the model.
     */
    protected function assignPosition(): Model
    {
        return $this->setPosition($this->getNextPosition());
    }

    /**
     * Determine the next position value in the model sequence.
     */
    protected function getNextPosition(): int
    {
        $maxPosition = $this->getMaxPosition();

        if (null === $maxPosition) {
            return $this->getInitPosition();
        }

        return $maxPosition + 1;
    }

    /**
     * Get the max position value in the model sequence.
     */
    protected function getMaxPosition(): ?int
    {
        return $this->newPositionQuery()->max($this->getPositionColumn());
    }

    /**
     * Shift models in a sequence before the move to a new position.
     */
    protected function shiftBeforeMove(int $newPosition, int $oldPosition): void
    {
        if ($newPosition < $oldPosition) {
            $this->newPositionQuery()->shiftToEnd($newPosition, $oldPosition);
        } elseif ($newPosition > $oldPosition) {
            $this->newPositionQuery()->shiftToStart($oldPosition, $newPosition);
        }
    }
}
