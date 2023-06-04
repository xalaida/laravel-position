<?php

namespace Nevadskiy\Position\Tests\App\Factories;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Position\Tests\App\Models\Category;

/**
 * @method static Category create(array $attributes = [])
 */
class CategoryFactory extends Factory
{
    /**
     * @inheritdoc
     */
    protected $model = Category::class;

    /**
     * Specify the given position to the model.
     */
    public function position(int $position): self
    {
        $this->attributes['position'] = $position;

        return $this;
    }
}
