<?php

namespace Nevadskiy\Position\Tests\Unit;

use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\Support\Models\Category;
use Nevadskiy\Position\Tests\TestCase;

class OrderTest extends TestCase
{
    /** @test */
    public function it_can_order_models_by_position(): void
    {
        $category2 = CategoryFactory::new()->onPosition(2)->create();
        $category0 = CategoryFactory::new()->onPosition(0)->create();
        $category1 = CategoryFactory::new()->onPosition(1)->create();

        $categories = Category::query()->orderByPosition()->get();

        self::assertTrue($categories[0]->is($category0));
        self::assertTrue($categories[1]->is($category1));
        self::assertTrue($categories[2]->is($category2));
    }

    /** @test */
    public function it_can_order_models_by_inverse_position(): void
    {
        $category2 = CategoryFactory::new()->onPosition(2)->create();
        $category0 = CategoryFactory::new()->onPosition(0)->create();
        $category1 = CategoryFactory::new()->onPosition(1)->create();

        $categories = Category::query()->orderByInversePosition()->get();

        self::assertTrue($categories[0]->is($category2));
        self::assertTrue($categories[1]->is($category1));
        self::assertTrue($categories[2]->is($category0));
    }
}
