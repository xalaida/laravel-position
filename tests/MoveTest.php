<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\App\Models\Category;

class MoveTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_move_model_to_decrease_position(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        $categories[2]->move(0);

        static::assertSame(0, $categories[2]->fresh()->getPosition());
        static::assertSame(1, $categories[0]->fresh()->getPosition());
        static::assertSame(2, $categories[1]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_can_move_model_to_increase_position(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        $categories[0]->move(2);

        static::assertSame(0, $categories[1]->fresh()->getPosition());
        static::assertSame(1, $categories[2]->fresh()->getPosition());
        static::assertSame(2, $categories[0]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_does_not_move_model_to_the_same_position(): void
    {
        $category = CategoryFactory::new()->position(3)->create();

        Category::query()->getConnection()->enableQueryLog();

        $result = $category->move(3);

        static::assertEmpty(Category::query()->getConnection()->getQueryLog());
        static::assertFalse($result);
    }

    /**
     * @test
     */
    public function it_can_update_position_without_moving_others(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        Category::withoutShiftingPosition(function () use ($categories) {
            $categories[0]->move(2);
        });

        static::assertSame(2, $categories[0]->fresh()->getPosition());
        static::assertSame(1, $categories[1]->fresh()->getPosition());
        static::assertSame(2, $categories[2]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_can_move_to_end_using_negative_position(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        $categories[0]->move(-1);

        static::assertSame(2, $categories[0]->fresh()->getPosition());
        static::assertSame(0, $categories[1]->fresh()->getPosition());
        static::assertSame(1, $categories[2]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_can_move_to_new_position_using_negative_position(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        $categories[0]->move(-2);

        static::assertSame(1, $categories[0]->fresh()->getPosition());
        static::assertSame(0, $categories[1]->fresh()->getPosition());
        static::assertSame(2, $categories[2]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_can_create_with_negative_position(): void
    {
        $category = new Category();
        $category->setPosition(-1);
        $category->save();

        static::assertSame(0, $category->fresh()->getPosition());
    }
}
