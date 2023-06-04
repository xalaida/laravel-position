<?php

namespace Nevadskiy\Position\Tests\App\Factories;

use Nevadskiy\Position\Tests\App\Models\Book;
use Nevadskiy\Position\Tests\App\Models\Category;

/**
 * @method static Book create(array $attributes = [])
 */
class BookFactory extends Factory
{
    /**
     * @inheritdoc
     */
    protected $model = Book::class;

    /**
     * Specify the given position to the model.
     */
    public function position(int $position): self
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
    protected function getDefaults(): array
    {
        return [
            'category_id' => CategoryFactory::new(),
        ];
    }
}
