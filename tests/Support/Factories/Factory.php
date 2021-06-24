<?php

namespace Nevadskiy\Position\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Model;

abstract class Factory
{
    /**
     * The override attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Make a new factory instance.
     *
     * @return static
     */
    public static function new(): Factory
    {
        return new static();
    }

    abstract protected function newModel(): Model;

    /**
     * Make a new model instance and save it into the database.
     */
    public function create(array $attributes = []): Model
    {
        $model = $this->newModel();

        foreach (array_merge($this->getDefaults(), $this->attributes, $attributes) as $attribute => $value) {
            $model->setAttribute(
                $attribute,
                $value instanceof Factory
                    ? $value->create()->getKey()
                    : $value
            );
        }

        $model->save();

        return $model;
    }

    /**
     * Get the default values.
     */
    protected function getDefaults(): array
    {
        return [];
    }
}
