[![Stand With Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner-direct-single.svg)](https://stand-with-ukraine.pp.ua)

# 🔢 Arrange Laravel models in a given order

[![PHPUnit](https://img.shields.io/github/actions/workflow/status/nevadskiy/laravel-position/phpunit.yml?branch=master)](https://packagist.org/packages/nevadskiy/laravel-position)
[![Code Coverage](https://img.shields.io/codecov/c/github/nevadskiy/laravel-position?token=9X6AQQYCPA)](https://packagist.org/packages/nevadskiy/laravel-position)
[![Latest Stable Version](https://img.shields.io/packagist/v/nevadskiy/laravel-position)](https://packagist.org/packages/nevadskiy/laravel-position)
[![License](https://img.shields.io/github/license/nevadskiy/laravel-position)](https://packagist.org/packages/nevadskiy/laravel-position)

## ✅ Requirements

- Laravel `7.0` or newer
- PHP `7.2` or newer

## 🔌 Installation

Install the package via Composer:

```bash
composer require nevadskiy/laravel-position
````

## 🔨 Add positions to models

Add the `HasPosition` trait to the models that should have positions:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Position\HasPosition;

class Category extends Model
{
    use HasPosition;
}
```

You also need to add a `position` column to the model's table using a migration:

```php
Schema::create('categories', function (Blueprint $table) {
    $table->integer('position')->unsigned()->index();
});
```

And that's it!

## 📄 Documentation

### How it works

Models with positions have an integer attribute named position, which indicates their `position` in the sequence. 
This attribute is automatically calculated upon insertion and is utilized for sorting the models during query operations.

### Creating models

The `position` attribute is a kind of array index and is automatically inserted when a new model is created. For example:

```php
$category = Category::create();
echo $category->position; // 0

$category = Category::create();
echo $category->position; // 1

$category = Category::create();
echo $category->position; // 2
```

#### Starting position

By default, the first record in the sequence is assigned a position value of `0`. If you want to specify a custom number to start counting models, you can override the `getStartPosition` method in your model:

```php
public function getStartPosition(): int
{
    return 1;
}
```

In this example, the first record will be assigned a position value of `1`.

#### Ordering

By default, the newly created model is assigned the position at the end of the sequence.

For example, if you want to create models at the beginning of the sequence, you can override the `getNextPosition` method in your model:

```php
public function getNextPosition(): int
{
    return $this->getStartPosition();
}
```

In this example, each new model will be assigned the starting position and will be positioned at the beginning of the sequence. The positions of other models in the sequence will be automatically updated:

```php
$first = Category::create();
echo $first->position; // 0

$second = Category::create();
echo $second->position; // 0
echo $first->position; // 1 (automatically updated)

$third = Category::create();
echo $third->position; // 0
echo $second->position; // 1 (automatically updated)
echo $first->position; // 2 (automatically updated again)
```

> You can also use negative positions. For example, the -1 position indicates that the model will be positioned at the end of the sequence. It is almost identical to Model::count() - 1. This is the default behavior.

### Deleting models

When a model is deleted, the positions of other records in the sequence are automatically updated.

### Querying models 

To select models in the arranged sequence, you can use the `orderByPosition` scope. For example:

```php
Category::orderByPosition()->get();
```

### Automatic ordering when querying models

The `orderByPosition` scope is not applied by default because the corresponding SQL statement will be added to all queries, even where it is not required.

It is recommended to manually add the scope in all places where you need it.

However, if you want to enable auto-ordering for all query operations, you can override the `alwaysOrderByPosition` method in your model as following:

```php
public function alwaysOrderByPosition(): bool
{
    return true;
}
```

### Moving items

#### Update

To move a model to an arbitrary position in the sequence, you can simply update its position. For example:

```php
$category->update([
    'position' => 3
]);
```

The positions of other models will be automatically recalculated as well.

#### Move

You can also use the `move` method, which sets a new position value and updates the model immediately. For example:

```php
$category->move(3);
```

If you want to move the model to the end of the sequence, you can use a negative position value. For example:

```php
$category->move(-1); // Move to the end
```

#### Swap

The `swap` method swaps the position of two models. For example:

```php
$category->swap($anotherCategory);
```

#### Arrange models

It is also possible to arrange models by their IDs.
The position of each model will be recalculated according to the index of its ID in the given array.
You can also provide a second argument as a starting position. For example:

```php
Category::arrangeByKeys([3, 5, 7]);
```

### Grouping models

Position grouping is particularly useful when you want to maintain position sequences separately for different groups of records within the same table.

To enable position grouping, you can specify one or more database columns that act as the grouping criteria for positions using the `groupPositionBy` method in your model:

```php
public function groupPositionBy(): array
{
    return [
        'category_id',
    ];
}
```

### Position lock

By default, when the position or group of a model is changed, the `PositionObserver` syncs positions of other models in the sequence accordingly using extra database queries.
If you want to disable this for some reason, you can disable the `PositionObserver` for a specific model like so:

```php
use Nevadskiy\Position\PositionObserver;

PositionObserver::lockFor(Category::class);

$category->update(['position' => 1]);

PositionObserver::unlockFor(Category::class);
```

## 📑 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## ☕ Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for more information.

## 🔓 Security

If you discover any security related issues, please [e-mail me](mailto:nevadskiy@gmail.com) instead of using the issue tracker.

## 📜 License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.

## 🛠️ Todo List

- [ ] support `swap` for models from different groups
