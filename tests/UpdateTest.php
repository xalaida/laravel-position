<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;

class UpdateTest extends TestCase
{
    /**
     * @test
     */
    public function it_updates_position_to_less_than_previous(): void
    {
        $category0 = CategoryFactory::new()->position(0)->create();
        $category1 = CategoryFactory::new()->position(1)->create();
        $category2 = CategoryFactory::new()->position(2)->create();

        $category2->update([
            'position' => 0,
        ]);

        static::assertSame(0, $category2->fresh()->getPosition());
        static::assertSame(1, $category0->fresh()->getPosition());
        static::assertSame(2, $category1->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_can_move_model_to_greater_than_previous(): void
    {
        $category0 = CategoryFactory::new()->position(0)->create();
        $category1 = CategoryFactory::new()->position(1)->create();
        $category2 = CategoryFactory::new()->position(2)->create();

        $category0->update([
            'position' => 2,
        ]);

        static::assertSame(0, $category1->fresh()->getPosition());
        static::assertSame(1, $category2->fresh()->getPosition());
        static::assertSame(2, $category0->fresh()->getPosition());
    }
}
