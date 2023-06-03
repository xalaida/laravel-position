<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\App\Models\Category;

class CreateTest extends TestCase
{
    /**
     * @test
     */
    public function it_assigns_position_value_when_model_is_creating(): void
    {
        $category = CategoryFactory::new()->create();

        static::assertSame(0, $category->position);
    }

    /**
     * @test
     */
    public function it_creates_model_at_end_of_sequence(): void
    {
        CategoryFactory::new()->createMany(2);

        $category = CategoryFactory::new()->create();

        static::assertSame(2, $category->position);
    }

    /**
     * @test
     */
    public function it_executes_2_queries_to_create_model_at_end_of_sequence(): void
    {
        CategoryFactory::new()->createMany(2);

        Category::query()->getConnection()->enableQueryLog();

        CategoryFactory::new()->create();

        self::assertCount(2, Category::query()->getConnection()->getQueryLog());
    }

    /**
     * @test
     */
    public function it_does_not_override_position_value_if_it_is_set_already(): void
    {
        $category = CategoryFactory::new()
            ->position(15)
            ->create();

        static::assertSame(15, $category->position);
    }

    /**
     * @test
     */
    public function it_can_create_model_in_middle_of_sequence(): void
    {
        $categories = CategoryFactory::new()->createMany(2);

        $category = CategoryFactory::new()
            ->position(1)
            ->create();

        static::assertSame(1, $category->position);
        static::assertSame($categories[0]->fresh()->position, 0);
        static::assertSame($categories[1]->fresh()->position, 2);
    }

    /**
     * @test
     */
    public function it_can_create_model_at_start_of_sequence(): void
    {
        $categories = CategoryFactory::new()->createMany(2);

        $category = CategoryFactory::new()
            ->position(0)
            ->create();

        static::assertSame(0, $category->position);
        static::assertSame($categories[0]->fresh()->position, 1);
        static::assertSame($categories[1]->fresh()->position, 2);
    }

    /**
     * @test
     */
    public function it_can_automatically_create_models_at_start_of_sequence(): void
    {
        $categories = CategoryFactory::new()
            ->using(ReverseCategory::class)
            ->createMany(2);

        $category = new ReverseCategory();
        $category->save();

        static::assertSame(0, $category->position);
        static::assertSame($categories[1]->fresh()->position, 1);
        static::assertSame($categories[0]->fresh()->position, 2);
    }
}

class ReverseCategory extends Category
{
    public function getNextPosition(): int
    {
        return 0;
    }
}
