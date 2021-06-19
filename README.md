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

### How it works

Models have a 'position' field with an unsigned integer value that is used for their ordering.


### Creating models

The position field serves as a sort of array index and is automatically inserted when creating a new record.

By default, the model takes a position at the very end of the sequence.


### Deleting models

When a record is deleted, the positions of another records in the sequence are updated automatically.


### Querying models 

To query models in the arranged sequence, use the `orderByPosition` scope.

```php
Category::orderByPosition()->get();
```


### Auto ordering

The `orderByPosition` scope is not applied by default because the appropriate SQL-statement will be added to all queries, even where it is not required.

It is much easier to manually add the scope in all places where you actually need it.

However, if you really want to enable auto-ordering, you can override `alwaysOrderByPosition` method in your model like this:

```php
public function alwaysOrderByPosition(): bool
{
    return true;
}
```


### Available methods


#### Swap

The `swap` method swaps the position of two models.

```php
$category1->swap($category3);
```
