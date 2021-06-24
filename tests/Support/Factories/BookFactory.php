<?php

namespace Nevadskiy\Position\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Position\Tests\Support\Models\Book;
use Nevadskiy\Position\Tests\Support\Models\Category;

/**
 * @method static Book create(array $attributes = [])
 */
class BookFactory extends Factory
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
     * Attach the given category on the model.
     */
    public function forCategory(Category $category): self
    {
        $this->attributes['category_id'] = $category->getKey();

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function newModel(): Model
    {
        return new Book();
    }

    /**
     * @inheritDoc
     */
    protected function getDefaults(): array
    {
        return [
            'category_id' => CategoryFactory::new(),
        ];
    }
}
