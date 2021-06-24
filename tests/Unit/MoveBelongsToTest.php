<?php

namespace Nevadskiy\Position\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Nevadskiy\Position\Tests\Support\Factories\BookFactory;
use Nevadskiy\Position\Tests\Support\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\TestCase;

class MoveBelongsToTest extends TestCase
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

        self::assertEquals(0, $book0->getPosition());
        self::assertEquals(1, $book1->getPosition());
        self::assertEquals(0, $anotherBook->getPosition());

        $book1->move(0);

        self::assertEquals(0, $book1->fresh()->getPosition());
        self::assertEquals(1, $book0->fresh()->getPosition());
        self::assertEquals(0, $anotherBook->fresh()->getPosition());
    }
}
