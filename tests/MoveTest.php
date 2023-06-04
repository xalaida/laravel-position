<?php

namespace Nevadskiy\Position\Tests;

use Carbon\Carbon;
use Nevadskiy\Position\PositioningScope;
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
    public function it_can_create_single_model_with_negative_position(): void
    {
        $category = new Category();
        $category->setPosition(-1);
        $category->save();

        static::assertSame(0, $category->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_can_shift_other_models_with_preserving_timestamps(): void
    {
        Carbon::setTestNow($yesterday = now()->subDay()->startOfSecond());

        $category1 = new Category();
        $category1->save();

        $category2 = new Category();
        $category2->save();

        Carbon::setTestNow($now = $yesterday->clone()->addDay());

        $category1->move(1);

        $category1->refresh();
        $category2->refresh();

        static::assertEquals(1, $category1->getPosition());
        static::assertTrue($category1->updated_at->eq($now));
        static::assertEquals(0, $category2->getPosition());
        static::assertTrue($category2->updated_at->eq($yesterday));
    }


    /**
     * @test
     */
    public function it_can_shift_other_models_without_preserving_timestamps(): void
    {
        Carbon::setTestNow($yesterday = now()->subDay()->startOfSecond());

        $category1 = new Category();
        $category1->save();

        $category2 = new Category();
        $category2->save();

        Carbon::setTestNow($now = $yesterday->clone()->addDay());

        PositioningScope::shiftWithTimestamps();

        $category1->move(1);

        $category1->refresh();
        $category2->refresh();

        static::assertEquals(1, $category1->getPosition());
        static::assertTrue($category1->updated_at->eq($now));
        static::assertEquals(0, $category2->getPosition());
        static::assertTrue($category2->updated_at->eq($now));

        PositioningScope::shiftWithTimestamps(false);
    }
}
