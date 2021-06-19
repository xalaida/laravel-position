<?php

namespace Nevadskiy\Position\Tests\Unit;

use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\Support\Models\Category;
use Nevadskiy\Position\Tests\TestCase;

class SwapTest extends TestCase
{
    // it does not break another positions

    /** @test */
    public function it_can_swap_models(): void
    {
        $category0 = CategoryFactory::new()->create();
        $category1 = CategoryFactory::new()->create();
        $category2 = CategoryFactory::new()->create();

        (new Category())->swap($category0, $category2);

        self::assertEquals($category0->fresh()->getPosition(), 2);
        self::assertEquals($category2->fresh()->getPosition(), 0);
    }
}
