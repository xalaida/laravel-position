<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\Support\Models\Category;

class ArrangeTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_arrange_models_by_keys(): void
    {
        $category0 = CategoryFactory::new()->create();
        $category1 = CategoryFactory::new()->create();
        $category2 = CategoryFactory::new()->create();
        $category3 = CategoryFactory::new()->create();
        $category4 = CategoryFactory::new()->create();

        Category::arrangeByKeys([
            $category2->getKey(),
            $category3->getKey(),
            $category0->getKey(),
            $category4->getKey(),
            $category1->getKey(),
        ]);

        static::assertSame(0, $category2->fresh()->getPosition());
        static::assertSame(1, $category3->fresh()->getPosition());
        static::assertSame(2, $category0->fresh()->getPosition());
        static::assertSame(3, $category4->fresh()->getPosition());
        static::assertSame(4, $category1->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_can_arrange_models_with_start_positions_by_keys(): void
    {
        $startPosition = 5;

        $category0 = CategoryFactory::new()->create();
        $category1 = CategoryFactory::new()->create();
        $category2 = CategoryFactory::new()->create();
        $category3 = CategoryFactory::new()->create();
        $category4 = CategoryFactory::new()->create();

        Category::arrangeByKeys([
            $category2->getKey(),
            $category3->getKey(),
            $category0->getKey(),
            $category4->getKey(),
            $category1->getKey(),
        ], $startPosition);

        static::assertSame(0 + $startPosition, $category2->fresh()->getPosition());
        static::assertSame(1 + $startPosition, $category3->fresh()->getPosition());
        static::assertSame(2 + $startPosition, $category0->fresh()->getPosition());
        static::assertSame(3 + $startPosition, $category4->fresh()->getPosition());
        static::assertSame(4 + $startPosition, $category1->fresh()->getPosition());
    }
}
