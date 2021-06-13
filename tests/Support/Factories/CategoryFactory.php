<?php

namespace Nevadskiy\Position\Tests\Support\Factories;

use Nevadskiy\Position\Tests\Support\Models\Category;

class CategoryFactory
{
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
        $category->forceFill(array_merge($this->getDefaults(), $attributes));
        $category->save();

        return $category;
    }

    /**
     * Get the default values.
     */
    private function getDefaults(): array
    {
        return [];
    }
}
