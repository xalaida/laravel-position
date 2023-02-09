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
            $model->assignPosition();
        });

        static::created(static function (self $model) {
            if ($model->shouldShiftPosition()) {
                $model->newPositionQuery()->whereKeyNot($model->getKey())->shiftToEnd($model->getPosition());
                $model->disableShiftingPosition();
            }
        });

        static::updating(static function (self $model) {
            if ($model->isDirty($model->getPositionColumn())) {
                $model->enableShiftingPosition();
            }
        });

        static::updated(static function (self $model) {
            if ($model->shouldShiftPosition()) {
                if ($model->getPosition() < $model->getOriginal($model->getPositionColumn())) {
                    $model->newPositionQuery()->whereKeyNot($model->getKey())->shiftToEnd($model->getPosition(), $model->getOriginal($model->getPositionColumn()));
                } elseif ($model->getPosition() > $model->getOriginal($model->getPositionColumn())) {
                    $model->newPositionQuery()->whereKeyNot($model->getKey())->shiftToStart($model->getOriginal($model->getPositionColumn()), $model->getPosition());
                }

                $model->disableShiftingPosition();
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
    public function startPosition(): int
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

    /**
     * Specify that the model should shift position of other models in the sequence.
     */
    public function enableShiftingPosition(): self
    {
        $this->shiftPosition = true;

        return $this;
    }

    /**
     * Specify that the model should not shift position of other models in the sequence.
     */
    public function disableShiftingPosition(): self
    {
        $this->shiftPosition = true;

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
    public function setPosition(?int $position): Model
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

    /**
     * Assign the next position value to the model.
     */
    protected function assignPosition(): void
    {
        if ($this->getPosition() === null) {
            $this->setPosition($this->nextPosition());
        }

        if ($this->getPosition() !== null) {
            $this->enableShiftingPosition();
        } else {
            $this->setPosition($this->getEndPosition());
        }
    }

    /**
     * Get the next position in the sequence for the model.
     */
    protected function nextPosition(): ?int
    {
        return null;
    }

    /**
     * Determine the next position value in the model sequence.
     */
    protected function getEndPosition(): int
    {
        $maxPosition = $this->getMaxPosition();

        if (null === $maxPosition) {
            return $this->startPosition();
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
}
