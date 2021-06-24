<?php

namespace Nevadskiy\Position\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Position\Tests\Support\Models\Category;

/**
 * @method static Category create(array $attributes = [])
 */
class CategoryFactory extends Factory
{
    /**
     * Specify the given position to the model.
     */
    public function onPosition(int $position): self
    {
        $this->attributes['position'] = $position;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function newModel(): Model
    {
        return new Category();
    }
}
