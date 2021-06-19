<?php

namespace Nevadskiy\Position\Tests\Unit;

use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\Support\Models\Category;
use Nevadskiy\Position\Tests\TestCase;

class ArrangeTest extends TestCase
{
    public function test_it_can_arrange_models_by_keys(): void
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

    public function test_it_can_arrange_models_with_init_positions_by_keys(): void
    {
        $initPosition = 5;

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
        ], $initPosition);

        static::assertSame(0 + $initPosition, $category2->fresh()->getPosition());
        static::assertSame(1 + $initPosition, $category3->fresh()->getPosition());
        static::assertSame(2 + $initPosition, $category0->fresh()->getPosition());
        static::assertSame(3 + $initPosition, $category4->fresh()->getPosition());
        static::assertSame(4 + $initPosition, $category1->fresh()->getPosition());
    }
}
