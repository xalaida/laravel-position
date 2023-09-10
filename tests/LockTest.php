<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\App\Models\Category;

class LockTest extends TestCase
{
    /**
     * @test
     */
    public function it_locks_positions(): void
    {
        Category::query()->getConnection()->enableQueryLog();

        $categories = Category::withPositionLock(function () {
            return CategoryFactory::new()
                ->position(0)
                ->createMany(3);
        });

        static::assertCount(3, Category::query()->getConnection()->getQueryLog());
        static::assertSame(0, $categories[0]->fresh()->getPosition());
        static::assertSame(0, $categories[1]->fresh()->getPosition());
        static::assertSame(0, $categories[2]->fresh()->getPosition());
    }
}
