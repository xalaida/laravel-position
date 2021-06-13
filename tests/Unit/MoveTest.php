<?php

namespace Nevadskiy\Position\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\TestCase;

class MoveTest extends TestCase
{
    /** @test */
    public function it_can_move_model_to_decrease_position(): void
    {
        $category0 = CategoryFactory::new()->onPosition(0)->create();
        $category1 = CategoryFactory::new()->onPosition(1)->create();
        $category2 = CategoryFactory::new()->onPosition(2)->create();

        $category2->move(0);

        self::assertEquals($category2->fresh()->getPosition(), 0);
        self::assertEquals($category0->fresh()->getPosition(), 1);
        self::assertEquals($category1->fresh()->getPosition(), 2);
    }

    /** @test */
    public function it_can_move_model_to_increase_position(): void
    {
        $category0 = CategoryFactory::new()->onPosition(0)->create();
        $category1 = CategoryFactory::new()->onPosition(1)->create();
        $category2 = CategoryFactory::new()->onPosition(2)->create();

        $category0->move(2);

        self::assertEquals($category1->fresh()->getPosition(), 0);
        self::assertEquals($category2->fresh()->getPosition(), 1);
        self::assertEquals($category0->fresh()->getPosition(), 2);
    }

    /** @test */
    public function it_does_not_move_model_to_the_same_position(): void
    {
        $category = CategoryFactory::new()->onPosition(3)->create();

        DB::connection()->enableQueryLog();

        $result = $category->move(3);

        self::assertEmpty(DB::connection()->getQueryLog());
        self::assertFalse($result);
    }
}
