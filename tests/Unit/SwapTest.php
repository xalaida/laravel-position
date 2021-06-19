<?php

namespace Nevadskiy\Position\Tests\Unit;

use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\TestCase;

class SwapTest extends TestCase
{
    public function test_it_can_swap_models(): void
    {
        $category0 = CategoryFactory::new()->create();
        $category1 = CategoryFactory::new()->create();
        $category2 = CategoryFactory::new()->create();

        $category0->swap($category2);

        static::assertSame($category0->fresh()->getPosition(), 2);
        static::assertSame($category2->fresh()->getPosition(), 0);
    }

    public function test_it_does_not_break_another_positions(): void
    {
        $category0 = CategoryFactory::new()->create();
        $category1 = CategoryFactory::new()->create();
        $category2 = CategoryFactory::new()->create();

        $category2->swap($category0);

        static::assertSame($category1->fresh()->getPosition(), 1);
    }
}
