# Laravel Position

The package allows you to arrange the laravel models in a given order.


## 🔌 Installation

Install the package via composer.

```bash
composer require nevadskiy/laravel-position
````


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


## 📄 Documentation

### Description

Models have a 'position' field with an unsigned integer value that is used for their ordering.

The position field serves as a sort of array index and is automatically inserted when creating a new record. 

By default, the model takes a position at the very end of the sequence.
