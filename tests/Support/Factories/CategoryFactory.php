<?php

namespace Nevadskiy\Position\Tests\Support\Factories;

use Nevadskiy\Position\Tests\Support\Models\Category;

class CategoryFactory
{
    /**
     * The override attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Make a new factory instance.
     */
    public static function new(): self
    {
        return new static();
    }

    /**
     * Make a new model instance and save it into the database.
     */
    public function create(array $attributes = []): Category
    {
        $category = new Category();
        $category->forceFill(array_merge($this->getDefaults(), $this->attributes, $attributes));
        $category->save();

        return $category;
    }

    /**
     * Specify the given position to the model.
     */
    public function onPosition(int $position): CategoryFactory
    {
        $this->attributes['position'] = $position;

        return $this;
    }

    /**
     * Get the default values.
     */
    private function getDefaults(): array
    {
        return [];
    }
}
