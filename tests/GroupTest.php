<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\App\Factories\BookFactory;
use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;

class GroupTest extends TestCase
{
    /**
     * @test
     */
    public function it_does_not_shift_positions_of_other_groups_on_create(): void
    {
        $category = CategoryFactory::new()->create();

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(2);

        $anotherBook = BookFactory::new()->create();

        static::assertSame(0, $books[0]->getPosition());
        static::assertSame(1, $books[1]->getPosition());
        static::assertSame(0, $anotherBook->getPosition());
    }

    /**
     * @test
     */
    public function it_does_not_shift_positions_of_other_groups_on_update(): void
    {
        $category = CategoryFactory::new()->create();

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(2);

        $anotherBook = BookFactory::new()->create();

        static::assertSame(0, $books[0]->getPosition());
        static::assertSame(1, $books[1]->getPosition());
        static::assertSame(0, $anotherBook->getPosition());

        $books[1]->move(0);

        static::assertSame(0, $books[1]->fresh()->getPosition());
        static::assertSame(1, $books[0]->fresh()->getPosition());

        static::assertSame(0, $anotherBook->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_does_not_shift_positions_of_other_groups_on_delete(): void
    {
        $category = CategoryFactory::new()->create();

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(2);

        $anotherBook = BookFactory::new()
            ->position(2)
            ->create();

        $books[0]->delete();

        static::assertSame(0, $books[1]->fresh()->getPosition());
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

    /**
     * @test
     */
    public function it_updates_group_and_position_correctly(): void
    {
        $category = CategoryFactory::new()->create();
        $anotherCategory = CategoryFactory::new()->create();

        $categoryBooks = BookFactory::new()
            ->forCategory($category)
            ->createMany(3);

        $anotherCategoryBooks = BookFactory::new()
            ->forCategory($anotherCategory)
            ->createMany(3);

        $categoryBooks[0]->category()
            ->associate($anotherCategory)
            ->setPosition(1)
            ->save();

        static::assertSame(0, $categoryBooks[1]->fresh()->position);
        static::assertSame(1, $categoryBooks[2]->fresh()->position);

        static::assertSame(0, $anotherCategoryBooks[0]->fresh()->position);
        static::assertSame(1, $categoryBooks[0]->fresh()->position);
        static::assertSame(2, $anotherCategoryBooks[1]->fresh()->position);
        static::assertSame(3, $anotherCategoryBooks[2]->fresh()->position);
    }

    // @todo change group with reverse ordering.
    // @todo handle complex groups with multiple column.
    // @todo expose hooks to model for manually verifying if group is changed.
}
