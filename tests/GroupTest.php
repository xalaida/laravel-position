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

        $categoryBook1 = BookFactory::new()->forCategory($category)->create();
        $categoryBook2 = BookFactory::new()->forCategory($category)->create();
        $categoryBook3 = BookFactory::new()->forCategory($category)->create();

        static::assertSame(0, $categoryBook1->getPosition());
        static::assertSame(1, $categoryBook2->getPosition());
        static::assertSame(2, $categoryBook3->getPosition());

        $categoryBook2->category()
            ->associate($anotherCategory)
            ->save();

        static::assertSame(0, $categoryBook1->fresh()->getPosition());
        static::assertSame(0, $categoryBook2->fresh()->getPosition());
        static::assertSame(1, $categoryBook3->fresh()->getPosition());
    }

    // @todo change group & position together
    // @todo change group with reverse ordering
}
