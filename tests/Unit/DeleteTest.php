<?php

namespace Nevadskiy\Position\Tests\Unit;

use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\TestCase;

class DeleteTest extends TestCase
{
    /** @test */
    public function it_updates_position_values_on_another_model_delete(): void
    {
        $category0 = CategoryFactory::new()->create();
        $category1 = CategoryFactory::new()->create();
        $category2 = CategoryFactory::new()->create();

        self::assertEquals(2, $category2->getPosition());

        $category1->delete();

        self::assertEquals(1, $category2->fresh()->getPosition());
        self::assertEquals(0, $category0->fresh()->getPosition());
    }

    /** @test */
    public function it_does_not_update_positions_when_last_record_is_deleted(): void
    {
        $category0 = CategoryFactory::new()->create();
        $category1 = CategoryFactory::new()->create();
        $category2 = CategoryFactory::new()->create();

        $category2->delete();

        self::assertEquals(0, $category0->fresh()->getPosition());
        self::assertEquals(1, $category1->fresh()->getPosition());
    }
}
