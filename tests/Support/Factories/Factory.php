<?php

namespace Nevadskiy\Position\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Model;

abstract class Factory
{
    /**
     * The model class of the factory.
     *
     * @var string
     */
    protected $model;

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
    final public static function new(): self
    {
        return new static();
    }

    /**
     * Use the given model class for the factory.
     */
    final public function using(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Make a new model instance and save it into the database.
     */
    final public function create(array $attributes = []): Model
    {
        $model = $this->newModel();

        foreach (array_merge($this->getDefaults(), $this->attributes, $attributes) as $attribute => $value) {
            $model->setAttribute(
                $attribute,
                $value instanceof self
                    ? $value->create()->getKey()
                    : $value
            );
        }

        $model->save();

        return $model;
    }

    /**
     * Create many model instances and save them into the database.
     */
    final public function createMany(int $count, array $attributes = []): array
    {
        $models = [];

        for ($i = 0; $i < $count; $i++) {
            $models[] = $this->create($attributes);
        }

        return $models;
    }

    /**
     * Make a new model instance.
     */
    protected function newModel(): Model
    {
        $model = $this->model;

        return new $model();
    }

    /**
     * Get the default values.
     */
    protected function getDefaults(): array
    {
        return [];
    }
}
