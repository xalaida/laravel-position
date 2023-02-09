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
        $categories = CategoryFactory::new()->createMany(5);

        Category::arrangeByKeys([
            $categories[2]->getKey(),
            $categories[3]->getKey(),
            $categories[0]->getKey(),
            $categories[4]->getKey(),
            $categories[1]->getKey(),
        ]);

        static::assertSame(0, $categories[2]->fresh()->getPosition());
        static::assertSame(1, $categories[3]->fresh()->getPosition());
        static::assertSame(2, $categories[0]->fresh()->getPosition());
        static::assertSame(3, $categories[4]->fresh()->getPosition());
        static::assertSame(4, $categories[1]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_can_arrange_models_with_start_positions_by_keys(): void
    {
        $startPosition = 5;

        $categories = CategoryFactory::new()->createMany(5);

        Category::arrangeByKeys([
            $categories[2]->getKey(),
            $categories[3]->getKey(),
            $categories[0]->getKey(),
            $categories[4]->getKey(),
            $categories[1]->getKey(),
        ], $startPosition);

        static::assertSame(0 + $startPosition, $categories[2]->fresh()->getPosition());
        static::assertSame(1 + $startPosition, $categories[3]->fresh()->getPosition());
        static::assertSame(2 + $startPosition, $categories[0]->fresh()->getPosition());
        static::assertSame(3 + $startPosition, $categories[4]->fresh()->getPosition());
        static::assertSame(4 + $startPosition, $categories[1]->fresh()->getPosition());
    }
}
