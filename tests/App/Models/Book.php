<?php

namespace Nevadskiy\Position\Tests\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nevadskiy\Position\HasPosition;

/**
 * @property int id
 * @property int category_id
 * @property int position
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property-read Category category
 */
class Book extends Model
{
    use HasPosition;

    protected $table = 'books';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function groupPositionBy(): array
    {
        return [
            'category_id',
        ];
    }
}
