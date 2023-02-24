# 🔢 Arrange Laravel models in a given order

[![Tests](https://github.com/nevadskiy/laravel-position/workflows/tests/badge.svg)](https://packagist.org/packages/nevadskiy/laravel-position)
[![Code Coverage](https://codecov.io/gh/nevadskiy/laravel-position/branch/master/graphs/badge.svg?branch=master)]
[![License](http://poser.pugx.org/nevadskiy/laravel-position/license)](https://packagist.org/packages/nevadskiy/laravel-position)
[![Latest Stable Version](http://poser.pugx.org/nevadskiy/laravel-position/v)](https://packagist.org/packages/nevadskiy/laravel-position)

[![Stand With Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner-direct-single.svg)](https://stand-with-ukraine.pp.ua)

## ✅ Requirements

- Laravel `7.0` or newer
- PHP `7.2` or newer

## 🔌 Installation

Install the package via composer.

```bash
composer require nevadskiy/laravel-position
````

## 🔨 Add positions to models

Add the `HasPosition` trait to your models that should have positions.

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

Add a `position` column to the model tables.

```php
Schema::create('categories', function (Blueprint $table) {
    $table->integer('position')->unsigned()->index();
});
```

## 📄 Documentation

### How it works

Models simply have an integer `position` attribute corresponding to the model's position in the sequence, which is automatically calculated on write and used for sorting the models on reads.

### Creating models

The `position` attribute is a kind of array index and is automatically inserted when a new model is created.

```php
$category = Category::create();
echo $category->position; // 0

$category = Category::create();
echo $category->position; // 1

$category = Category::create();
echo $category->position; // 2
```

The starting position gets a `0` value by default. To change that, override the `startPosition` method in the model:

```php
public function startPosition(): int
{
    return 1;
}
```

By default, the created model takes a position at the very end of the sequence. If you need to customize that behaviour, you can override the `nextPosition` method:

```php
public function nextPosition(): ?int
{
    return $this->startPosition();
}
```

In that example, a new model will be created in the beginning of the sequence and the position of other models in the sequence will be automatically incremented.

### Deleting models

When a model is deleted, the positions of other records in the sequence are updated automatically.

### Querying models 

To select models in the arranged sequence, use the `orderByPosition` scope:

```php
Category::orderByPosition()->get();
```

### Automatic ordering

The `orderByPosition` scope is not applied by default because the corresponding SQL statement will be added to all queries, even where it is not required.

It is much easier to manually add the scope in all places where you need it.

However, if you want to enable auto-ordering, you can override the `alwaysOrderByPosition` method in your model like this:

```php
public function alwaysOrderByPosition(): bool
{
    return true;
}
```

### Moving items

#### Update

To move a model to an arbitrary position in the sequence, you can simply update its position like this:

```php
$category->update([
    'position' => 3
]);
```

The positions of other models will be automatically recalculated as well.

#### Move

You can also use the `move` method that sets a new position value and updates the model immediately:

```php
$category->move(3);
```

If you want to move the model to the end of the sequence, you can use a negative position value:

```php
$category->move(-1); // Move to the end
```

#### Swap

The `swap` method swaps the position of two models.

```php
$category->swap($anotherCategory);
```

#### Without shifting

By default, the package automatically updates the position of other models when the model position is updated.

If you want to update the model position without shifting the position of other models, you can use the `withoutShifting` method:

```php
Category::withoutShifting(function () {
    $category->move(5);
})
```

#### Arrange models

It is also possible to arrange models by their IDs.

The position of each model will be recalculated according to the index of its ID in the given array. 

You can also provide a second argument as a starting position.

```php
Category::arrangeByKeys([3, 5, 7]);
```

### Grouping / Dealing with relations

To allow models to be positioned within groups, you need to override the `newPositionQuery` method that should return a query to the grouped model sequence.

Using the relation builder:

```php
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nevadskiy\Position\HasPosition;

class Category
{
    use HasPosition;

    public function group(): BelongsTo
    {
        return $this->hasMany(Group::class);
    }

    protected function newPositionQuery(): Builder
    {
        return $this->group->categories();
    }
}
```

Using the `where` method:

```php
use Illuminate\Database\Eloquent\Builder;

protected function newPositionQuery(): Builder
{
    return $this->newQuery()->where('parent_id', $this->parent_id);
}
```

## 📑 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## ☕ Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for more information.

## 🔓 Security

If you discover any security related issues, please [e-mail me](mailto:nevadskiy@gmail.com) instead of using the issue tracker.

## 📜 License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.
