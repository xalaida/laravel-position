<?php

namespace Nevadskiy\Position\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nevadskiy\Position\HasPosition;
use Nevadskiy\Position\Tests\App\Factories\BookFactory;
use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\App\Models\Category;

class GroupTest extends TestCase
{
    /**
     * @test
     */
    public function it_sets_positions_correctly_for_different_groups(): void
    {
        [$category, $anotherCategory] = CategoryFactory::new()->createMany(2);

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(2);

        $anotherBooks = BookFactory::new()
            ->forCategory($anotherCategory)
            ->createMany(2);

        static::assertSame(0, $books[0]->getPosition());
        static::assertSame(1, $books[1]->getPosition());
        static::assertSame(0, $anotherBooks[0]->getPosition());
        static::assertSame(1, $anotherBooks[1]->getPosition());
    }

    /**
     * @test
     */
    public function it_does_not_shift_positions_of_other_groups_on_create(): void
    {
        $category = CategoryFactory::new()->create();

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(2);

        $anotherBook = BookFactory::new()
            ->position(0)
            ->create();

        static::assertSame(0, $anotherBook->getPosition());
        static::assertSame(1, $books[1]->fresh()->getPosition());
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
    public function it_syncs_positions_when_group_is_changed(): void
    {
        $category = CategoryFactory::new()->create();
        $anotherCategory = CategoryFactory::new()->create();

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(3);

        static::assertSame(0, $books[0]->getPosition());
        static::assertSame(1, $books[1]->getPosition());
        static::assertSame(2, $books[2]->getPosition());

        BookFactory::new()
            ->forCategory($anotherCategory)
            ->create();

        $books[1]->category()
            ->associate($anotherCategory)
            ->save();

        static::assertSame(0, $books[0]->fresh()->getPosition());
        static::assertSame(1, $books[1]->fresh()->getPosition());
        static::assertSame(1, $books[2]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_sets_next_position_correctly_when_group_is_changed(): void
    {
        $category = CategoryFactory::new()->create();
        $anotherCategory = CategoryFactory::new()->create();

        $book = BookFactory::new()
            ->forCategory($category)
            ->create();

        $anotherBook = BookFactory::new()
            ->forCategory($anotherCategory)
            ->create();

        $book->category()
            ->associate($anotherCategory)
            ->save();

        static::assertSame(0, $anotherBook->fresh()->getPosition());
        static::assertSame(1, $book->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_updates_group_along_with_position_correctly(): void
    {
        $category = CategoryFactory::new()->create();
        $anotherCategory = CategoryFactory::new()->create();

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(3);

        $anotherBooks = BookFactory::new()
            ->forCategory($anotherCategory)
            ->createMany(3);

        $books[0]->category()
            ->associate($anotherCategory)
            ->setPosition(1)
            ->save();

        static::assertSame(0, $books[1]->fresh()->position);
        static::assertSame(1, $books[2]->fresh()->position);

        static::assertSame(0, $anotherBooks[0]->fresh()->position);
        static::assertSame(1, $books[0]->fresh()->position);
        static::assertSame(2, $anotherBooks[1]->fresh()->position);
        static::assertSame(3, $anotherBooks[2]->fresh()->position);
    }

    /**
     * @test
     */
    public function it_executes_3_queries_to_move_model_at_end_of_sequence_of_another_group(): void
    {
        $category = CategoryFactory::new()->create();
        $anotherCategory = CategoryFactory::new()->create();

        $book = BookFactory::new()
            ->forCategory($category)
            ->create();

        Category::query()->getConnection()->enableQueryLog();

        $book->category()
            ->associate($anotherCategory)
            ->save();

        self::assertCount(3, Category::query()->getConnection()->getQueryLog());
    }

    /**
     * @test
     */
    public function it_moves_model_at_end_of_sequence_of_another_group(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('status');
            $table->integer('position')->unsigned();
            $table->timestamps();
        });

        $cancelledApplication = Application::create([
            'user_id' => 1,
            'status' => 'cancelled',
            'position' => 0,
        ]);

        $newApplications = [];

        foreach (range(0, 3) as $position) {
            $newApplications[] = Application::create([
                'user_id' => 1,
                'status' => 'new',
                'position' => $position,
            ]);
        }

        $cancelledApplication->update([
            'status' => 'new',
            'position' => 4,
        ]);

        static::assertSame(4, $cancelledApplication->fresh()->position);
        static::assertSame('new', $cancelledApplication->fresh()->status);

        static::assertSame(0, $newApplications[0]->fresh()->position);
        static::assertSame(1, $newApplications[1]->fresh()->position);
        static::assertSame(2, $newApplications[2]->fresh()->position);
        static::assertSame(3, $newApplications[3]->fresh()->position);

        Schema::drop('applications');
    }
}

class Application extends Model
{
    use HasPosition;

    protected $fillable = [
        'user_id',
        'status',
        'position',
    ];

    public function groupPositionBy(): array
    {
        return [
            'user_id',
            'status',
        ];
    }
}
