<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\PositionObserver;
use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\App\Models\Category;

class LockTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_lock_positions(): void
    {
        PositionObserver::lockFor(Category::class);

        Category::query()->getConnection()->enableQueryLog();

        $categories = CategoryFactory::new()
            ->position(0)
            ->createMany(3);

        static::assertCount(3, Category::query()->getConnection()->getQueryLog());
        static::assertSame(0, $categories[0]->fresh()->getPosition());
        static::assertSame(0, $categories[1]->fresh()->getPosition());
        static::assertSame(0, $categories[2]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_locks_position_for_created_models(): void
    {
        Category::query()->getConnection()->enableQueryLog();

        $categories = CategoryFactory::new()->createMany(3);

        static::assertCount(3, Category::query()->getConnection()->getQueryLog());
        static::assertSame(0, $categories[0]->fresh()->getPosition());
        static::assertSame(0, $categories[1]->fresh()->getPosition());
        static::assertSame(0, $categories[2]->fresh()->getPosition());

        Category::unlockPositions();
    }

    /**
     * @test
     */
    public function it_locks_position_for_reverse_models(): void
    {
        Category::lockPositions(0);

        Category::query()->getConnection()->enableQueryLog();

        $categories = CategoryFactory::new()
            ->using(ReverseCategory::class)
            ->createMany(3);

        static::assertCount(3, Category::query()->getConnection()->getQueryLog());
        static::assertSame(0, $categories[0]->fresh()->getPosition());
        static::assertSame(0, $categories[1]->fresh()->getPosition());
        static::assertSame(0, $categories[2]->fresh()->getPosition());

        Category::unlockPositions();
    }

    /**
     * @test
     */
    public function it_locks_position_using_static_counter(): void
    {
        Category::lockPositions(static function () {
            static $count = 0;

            return $count++;
        });

        Category::query()->getConnection()->enableQueryLog();

        $categories = CategoryFactory::new()->createMany(3);

        static::assertCount(3, Category::query()->getConnection()->getQueryLog());
        static::assertSame(0, $categories[0]->fresh()->getPosition());
        static::assertSame(1, $categories[1]->fresh()->getPosition());
        static::assertSame(2, $categories[2]->fresh()->getPosition());

        Category::unlockPositions();
    }
}
