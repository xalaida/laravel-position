<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\App\Models\Category;

class ForceTest extends TestCase
{
    /**
     * @test
     */
    public function it_forces_position(): void
    {
        Category::forcePosition(0);

        $categories = CategoryFactory::new()->createMany(3);

        static::assertSame(0, $categories[0]->getPosition());
        static::assertSame(0, $categories[1]->getPosition());
        static::assertSame(0, $categories[2]->getPosition());
    }
}
