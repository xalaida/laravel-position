<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;

class SetPositionTest extends TestCase
{
    /** @test */
    public function it_sets_position_value_on_model_create(): void
    {
        $category = CategoryFactory::new()->create();

        self::assertEquals(0, $category->position);
    }
}
