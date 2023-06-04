<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;

class DeleteTest extends TestCase
{
    /**
     * @test
     */
    public function it_updates_position_values_on_another_model_delete(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        static::assertSame(2, $categories[2]->getPosition());

        $categories[1]->delete();

        static::assertSame(1, $categories[2]->fresh()->getPosition());
        static::assertSame(0, $categories[0]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_does_not_update_positions_when_last_record_is_deleted(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        $categories[2]->delete();

        static::assertSame(0, $categories[0]->fresh()->getPosition());
        static::assertSame(1, $categories[1]->fresh()->getPosition());
    }
}
