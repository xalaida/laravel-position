<?php

namespace Nevadskiy\Position\Tests\Unit;

use Nevadskiy\Position\Tests\Support\Factories\BookFactory;
use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\TestCase;

class ScopePositionQueryTest extends TestCase
{
    public function test_it_positioned_within_its_relation_scope(): void
    {
        $category = CategoryFactory::new()->create();

        $book0 = BookFactory::new()
            ->forCategory($category)
            ->create();

        $book1 = BookFactory::new()
            ->forCategory($category)
            ->create();

        $anotherBook = BookFactory::new()->create();

        static::assertSame(0, $book0->getPosition());
        static::assertSame(1, $book1->getPosition());
        static::assertSame(0, $anotherBook->getPosition());

        $book1->move(0);

        static::assertSame(0, $book1->fresh()->getPosition());
        static::assertSame(1, $book0->fresh()->getPosition());
        static::assertSame(0, $anotherBook->fresh()->getPosition());
    }

    public function test_it_does_not_update_position_values_that_are_out_of_scope_on_delete(): void
    {
        $category = CategoryFactory::new()->create();

        $book0 = BookFactory::new()
            ->onPosition(0)
            ->forCategory($category)
            ->create();

        $book1 = BookFactory::new()
            ->forCategory($category)
            ->onPosition(1)
            ->create();

        $anotherBook = BookFactory::new()
            ->onPosition(2)
            ->create();

        $book0->delete();

        static::assertSame(0, $book1->fresh()->getPosition());
        static::assertSame(2, $anotherBook->fresh()->getPosition());
    }

    public function test_it_calculates_max_position_by_scoped_items(): void
    {
        $category0 = CategoryFactory::new()->create();
        $category1 = CategoryFactory::new()->create();

        BookFactory::new()->forCategory($category0)->create();
        BookFactory::new()->forCategory($category0)->create();

        BookFactory::new()->forCategory($category1)->create();
        $book = BookFactory::new()->forCategory($category1)->create();

        static::assertSame(1, $book->getPosition());
    }
}
