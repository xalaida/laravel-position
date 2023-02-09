<?php

namespace Nevadskiy\Position\Tests;

use Illuminate\Support\Facades\DB;
use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;

class ShiftTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_shift_model_to_decrease_position(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        $categories[2]->shift(0);

        static::assertSame(0, $categories[2]->fresh()->getPosition());
        static::assertSame(1, $categories[0]->fresh()->getPosition());
        static::assertSame(2, $categories[1]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_can_shift_model_to_increase_position(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        $categories[0]->shift(2);

        static::assertSame(0, $categories[1]->fresh()->getPosition());
        static::assertSame(1, $categories[2]->fresh()->getPosition());
        static::assertSame(2, $categories[0]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_does_not_shift_model_to_the_same_position(): void
    {
        $category = CategoryFactory::new()->position(3)->create();

        DB::connection()->enableQueryLog();

        $result = $category->shift(3);

        static::assertEmpty(DB::connection()->getQueryLog());
        static::assertFalse($result);
    }

//    /**
//     * @test
//     */
//    public function it_can_update_position_without_shifting_others(): void
//    {
//        $categories = CategoryFactory::new()->createMany(3);
//
//        Category::withoutShiftingPosition(function () use ($categories) {
//            $categories[0]->shift(2);
//        });
//
//        static::assertSame(2, $categories[0]->fresh()->getPosition());
//        static::assertSame(1, $categories[1]->fresh()->getPosition());
//        static::assertSame(2, $categories[2]->fresh()->getPosition());
//    }
}
