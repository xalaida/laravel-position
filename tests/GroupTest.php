<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\App\Factories\BookFactory;
use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;

class GroupTest extends TestCase
{
    /**
     * @test
     */
    public function it_groups_positions(): void
    {
        $category = CategoryFactory::new()->create();

        $book1 = BookFactory::new()
            ->forCategory($category)
            ->create();

        $book2 = BookFactory::new()
            ->forCategory($category)
            ->create();

        $anotherBook = BookFactory::new()->create();

        static::assertSame(0, $book1->getPosition());
        static::assertSame(1, $book2->getPosition());
        static::assertSame(0, $anotherBook->getPosition());

        $book2->move(0);

        static::assertSame(0, $book2->fresh()->getPosition());
        static::assertSame(1, $book1->fresh()->getPosition());
        static::assertSame(0, $anotherBook->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_does_not_update_position_values_that_are_out_of_scope_on_delete(): void
    {
        $category = CategoryFactory::new()->create();

        $book0 = BookFactory::new()
            ->position(0)
            ->forCategory($category)
            ->create();

        $book1 = BookFactory::new()
            ->forCategory($category)
            ->position(1)
            ->create();

        $anotherBook = BookFactory::new()
            ->position(2)
            ->create();

        $book0->delete();

        static::assertSame(0, $book1->fresh()->getPosition());
        static::assertSame(2, $anotherBook->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_calculates_max_position_by_scoped_items(): void
    {
        $category0 = CategoryFactory::new()->create();
        $category1 = CategoryFactory::new()->create();

        BookFactory::new()->forCategory($category0)->create();
        BookFactory::new()->forCategory($category0)->create();

        BookFactory::new()->forCategory($category1)->create();
        $book = BookFactory::new()->forCategory($category1)->create();

        static::assertSame(1, $book->getPosition());
    }

    /**
     * @test
     */
    public function it_syncs_positions_when_group_is_changed(): void
    {
        $category = CategoryFactory::new()->create();
        $anotherCategory = CategoryFactory::new()->create();

        $categoryBooks = BookFactory::new()
            ->forCategory($category)
            ->createMany(3);

        static::assertSame(0, $categoryBooks[0]->getPosition());
        static::assertSame(1, $categoryBooks[1]->getPosition());
        static::assertSame(2, $categoryBooks[2]->getPosition());

        BookFactory::new()
            ->forCategory($anotherCategory)
            ->create();

        $categoryBooks[1]->category()
            ->associate($anotherCategory)
            ->save();

        static::assertSame(0, $categoryBooks[0]->fresh()->getPosition());
        static::assertSame(1, $categoryBooks[1]->fresh()->getPosition());
        static::assertSame(1, $categoryBooks[2]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_sets_next_position_correctly_when_group_is_changed(): void
    {
        $category = CategoryFactory::new()->create();
        $anotherCategory = CategoryFactory::new()->create();

        $categoryBook = BookFactory::new()
            ->forCategory($category)
            ->create();

        $anotherCategoryBook = BookFactory::new()
            ->forCategory($anotherCategory)
            ->create();

        $categoryBook->category()
            ->associate($anotherCategory)
            ->save();

        static::assertSame(0, $anotherCategoryBook->fresh()->getPosition());
        static::assertSame(1, $categoryBook->fresh()->getPosition());
    }

    // @todo change group & position together.
    // @todo change group with reverse ordering.
    // @todo handle complex groups with multiple column.
    // @todo expose hooks to model for manually verifying if group is changed.
}
