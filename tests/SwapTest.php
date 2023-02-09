<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\Support\Models\Category;

class SwapTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_swap_models(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        $categories[0]->swap($categories[2]);

        static::assertSame($categories[0]->fresh()->getPosition(), 2);
        static::assertSame($categories[2]->fresh()->getPosition(), 0);
    }

    /**
     * @test
     */
    public function it_executes_only_2_queries_to_swap_models(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        Category::query()->getConnection()->enableQueryLog();

        $categories[0]->swap($categories[2]);

        self::assertCount(2, Category::query()->getConnection()->getQueryLog());
    }

    /**
     * @test
     */
    public function it_does_not_break_another_positions(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        $categories[2]->swap($categories[0]);

        static::assertSame($categories[1]->fresh()->getPosition(), 1);
    }
}
