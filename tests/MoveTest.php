<?php

namespace Nevadskiy\Position\Tests;

use Illuminate\Support\Facades\DB;
use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;

class MoveTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_move_model_to_decrease_position(): void
    {
        $category0 = CategoryFactory::new()->position(0)->create();
        $category1 = CategoryFactory::new()->position(1)->create();
        $category2 = CategoryFactory::new()->position(2)->create();

        $category2->move(0);

        static::assertSame(0, $category2->fresh()->getPosition());
        static::assertSame(1, $category0->fresh()->getPosition());
        static::assertSame(2, $category1->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_can_move_model_to_increase_position(): void
    {
        $category0 = CategoryFactory::new()->position(0)->create();
        $category1 = CategoryFactory::new()->position(1)->create();
        $category2 = CategoryFactory::new()->position(2)->create();

        $category0->move(2);

        static::assertSame(0, $category1->fresh()->getPosition());
        static::assertSame(1, $category2->fresh()->getPosition());
        static::assertSame(2, $category0->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_does_not_move_model_to_the_same_position(): void
    {
        $category = CategoryFactory::new()->position(3)->create();

        DB::connection()->enableQueryLog();

        $result = $category->move(3);

        static::assertEmpty(DB::connection()->getQueryLog());
        static::assertFalse($result);
    }
}
