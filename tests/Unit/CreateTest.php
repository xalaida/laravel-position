<?php

namespace Nevadskiy\Position\Tests\Unit;

use Mockery;
use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\Support\Models\Category;
use Nevadskiy\Position\Tests\TestCase;

class CreateTest extends TestCase
{
    /** @test */
    public function it_sets_position_value_on_model_create(): void
    {
        $category = CategoryFactory::new()->create();

        self::assertEquals(0, $category->position);
    }

    /** @test */
    public function it_sets_next_position_value_in_model_sequence(): void
    {
        $category0 = CategoryFactory::new()->create();
        $category1 = CategoryFactory::new()->create();
        $category2 = CategoryFactory::new()->create();

        self::assertEquals(2, $category2->position);
    }

    /** @test */
    public function it_does_not_override_position_value_if_it_is_set_already(): void
    {
        $category = CategoryFactory::new()->onPosition(15)->create();

        self::assertEquals(15, $category->position);
    }

    /** @test */
    public function it_can_configure_initial_position_value(): void
    {
        $fakeCategory = Mockery::mock(Category::class)->makePartial();
        $fakeCategory->shouldReceive('newInstance')->andReturnSelf();
        $fakeCategory->shouldReceive('getInitPosition')->andReturn(23);
        $fakeCategory->__construct();

        $category = Category::query()->setModel($fakeCategory)->create();

        self::assertEquals(23, $category->position);
    }
}