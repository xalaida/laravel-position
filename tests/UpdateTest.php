<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;

class UpdateTest extends TestCase
{
    /**
     * @test
     */
    public function it_updates_position_to_less_than_previous(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        $categories[2]->update([
            'position' => 0,
        ]);

        static::assertSame(0, $categories[2]->fresh()->getPosition());
        static::assertSame(1, $categories[0]->fresh()->getPosition());
        static::assertSame(2, $categories[1]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_can_move_model_to_greater_than_previous(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        $categories[0]->update([
            'position' => 2,
        ]);

        static::assertSame(0, $categories[1]->fresh()->getPosition());
        static::assertSame(1, $categories[2]->fresh()->getPosition());
        static::assertSame(2, $categories[0]->fresh()->getPosition());
    }
}
