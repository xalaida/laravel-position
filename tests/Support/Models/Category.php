<?php

namespace Nevadskiy\Position\Tests\Support\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Position\HasPosition;

/**
 * @property int id
 * @property int position
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Category extends Model
{
    use HasPosition;

    protected $table = 'categories';
}
