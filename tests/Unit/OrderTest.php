<?php

namespace Nevadskiy\Position\Tests\Unit;

use Mockery;
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

    /** @test */
    public function it_can_be_ordered_by_position_by_default(): void
    {
        $category2 = CategoryFactory::new()->onPosition(2)->create();
        $category0 = CategoryFactory::new()->onPosition(0)->create();
        $category1 = CategoryFactory::new()->onPosition(1)->create();

        $fakeCategory = Mockery::mock(new Category());
        $fakeCategory->shouldReceive('alwaysOrderByPosition')->andReturnTrue();

        $categories = Category::query()->setModel($fakeCategory)->get();

        self::assertTrue($categories[0]->is($category0));
        self::assertTrue($categories[1]->is($category1));
        self::assertTrue($categories[2]->is($category2));
    }

    /** @test */
    public function it_is_not_ordered_by_position_by_default(): void
    {
        $category2 = CategoryFactory::new()->onPosition(2)->create();
        $category0 = CategoryFactory::new()->onPosition(0)->create();
        $category1 = CategoryFactory::new()->onPosition(1)->create();

        $categories = Category::query()->get();

        self::assertTrue($categories[0]->is($category2));
        self::assertTrue($categories[1]->is($category0));
        self::assertTrue($categories[2]->is($category1));
    }
}
