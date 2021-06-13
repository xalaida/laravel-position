# Laravel Position

The package allows you to arrange the laravel models in a given order.


## 🔨 Add positions to models

1. Add the `HasPosition` trait to your models that should have positions.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Position\HasPosition;

class Post extends Model
{
    use HasPosition;
}
```

2. Add a `position` column to the model tables.

```php
Schema::create('...', function (Blueprint $table) {
    $table->integer('position')->unsigned()->index();
});
```
