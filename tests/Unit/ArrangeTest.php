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

        self::assertEquals(0, $category2->fresh()->getPosition());
        self::assertEquals(1, $category3->fresh()->getPosition());
        self::assertEquals(2, $category0->fresh()->getPosition());
        self::assertEquals(3, $category4->fresh()->getPosition());
        self::assertEquals(4, $category1->fresh()->getPosition());
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

        self::assertEquals(0 + $initPosition, $category2->fresh()->getPosition());
        self::assertEquals(1 + $initPosition, $category3->fresh()->getPosition());
        self::assertEquals(2 + $initPosition, $category0->fresh()->getPosition());
        self::assertEquals(3 + $initPosition, $category4->fresh()->getPosition());
        self::assertEquals(4 + $initPosition, $category1->fresh()->getPosition());
    }
}
