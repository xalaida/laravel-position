<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\App\Models\Category;

class LockTest extends TestCase
{
    /**
     * @test
     */
    public function it_locks_positions_on_create(): void
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

    /**
     * @test
     */
    public function it_locks_positions_on_delete(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        Category::query()->getConnection()->enableQueryLog();

        Category::withPositionLock(function () use ($categories) {
            $categories[0]->delete();
        });

        static::assertCount(1, Category::query()->getConnection()->getQueryLog());
        static::assertSame(1, $categories[1]->fresh()->getPosition());
        static::assertSame(2, $categories[2]->fresh()->getPosition());
    }
}
