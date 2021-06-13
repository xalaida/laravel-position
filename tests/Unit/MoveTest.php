<?php

namespace Nevadskiy\Position\Tests\Unit;

use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\TestCase;

class MoveTest extends TestCase
{
    /** @test */
    public function it_can_move_model_to_another_position(): void
    {
        $category0 = CategoryFactory::new()->onPosition(0)->create();
        $category1 = CategoryFactory::new()->onPosition(1)->create();
        $category2 = CategoryFactory::new()->onPosition(2)->create();

        $category2->move(0);

        self::assertEquals($category2->fresh()->getPosition(), 0);
        self::assertEquals($category0->fresh()->getPosition(), 1);
        self::assertEquals($category1->fresh()->getPosition(), 2);
    }
}
