<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\App\Models\Category;

class SwapTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_swap_models(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        $categories[0]->swap($categories[2]);

        static::assertSame(2, $categories[0]->fresh()->getPosition());
        static::assertSame(0, $categories[2]->fresh()->getPosition());
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
    public function it_does_not_shifts_position_of_other_models(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        $categories[2]->swap($categories[0]);

        static::assertSame(1, $categories[1]->fresh()->getPosition());
    }
}
