<?php

namespace Nevadskiy\Position\Tests\Unit;

use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\Support\Models\Category;
use Nevadskiy\Position\Tests\TestCase;

class ArrangeTest extends TestCase
{
    /** @test */
    public function it_can_arrange_models_by_ids(): void
    {
        $category0 = CategoryFactory::new()->create();
        $category1 = CategoryFactory::new()->create();
        $category2 = CategoryFactory::new()->create();
        $category3 = CategoryFactory::new()->create();
        $category4 = CategoryFactory::new()->create();

        (new Category())->arrangeByIds([
            $category2->getKey(),
            $category3->getKey(),
            $category0->getKey(),
            $category4->getKey(),
            $category1->getKey(),
        ]);

        self::assertEquals($category2->fresh()->getPosition(), 0);
        self::assertEquals($category3->fresh()->getPosition(), 1);
        self::assertEquals($category0->fresh()->getPosition(), 2);
        self::assertEquals($category4->fresh()->getPosition(), 3);
        self::assertEquals($category1->fresh()->getPosition(), 4);
    }

    /** @test */
    public function it_can_arrange_models_with_init_positions_by_ids(): void
    {
        $initPosition = 5;

        $category0 = CategoryFactory::new()->create();
        $category1 = CategoryFactory::new()->create();
        $category2 = CategoryFactory::new()->create();
        $category3 = CategoryFactory::new()->create();
        $category4 = CategoryFactory::new()->create();

        (new Category())->arrangeByIds([
            $category2->getKey(),
            $category3->getKey(),
            $category0->getKey(),
            $category4->getKey(),
            $category1->getKey(),
        ], $initPosition);

        self::assertEquals($category2->fresh()->getPosition(), 0 + $initPosition);
        self::assertEquals($category3->fresh()->getPosition(), 1 + $initPosition);
        self::assertEquals($category0->fresh()->getPosition(), 2 + $initPosition);
        self::assertEquals($category4->fresh()->getPosition(), 3 + $initPosition);
        self::assertEquals($category1->fresh()->getPosition(), 4 + $initPosition);
    }
}
