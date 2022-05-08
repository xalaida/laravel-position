# Laravel Position

[![Stand With Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner-direct-single.svg)](https://stand-with-ukraine.pp.ua)

[![Latest Stable Version](https://poser.pugx.org/nevadskiy/laravel-position/v)](https://packagist.org/packages/nevadskiy/laravel-position)
[![Tests](https://github.com/nevadskiy/laravel-position/workflows/tests/badge.svg)](https://packagist.org/packages/nevadskiy/laravel-position)
[![Code Coverage](https://codecov.io/gh/nevadskiy/laravel-position/branch/master/graphs/badge.svg?branch=master)](https://packagist.org/packages/nevadskiy/laravel-position)
[![License](https://poser.pugx.org/nevadskiy/laravel-position/license)](https://packagist.org/packages/nevadskiy/laravel-position)

The package allows you to arrange the laravel models in a given order.


## ✅ Requirements

- Laravel `7.0` or newer
- PHP `7.2` or newer


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

class Category extends Model
{
    use HasPosition;
}
```

2. Add a `position` column to the model tables.

```php
Schema::create('categories', function (Blueprint $table) {
    $table->integer('position')->unsigned()->index();
});
```


## 📄 Documentation

### How it works

Models have a 'position' field with an unsigned integer value that is used for their ordering.


### Creating models

The position field serves as a sort of array index and is automatically inserted when creating a new record.

By default, the model takes a position at the very end of the sequence.

The first position gets an initial value of `0` by default. To change that, override the `getInitPosition` method

```php
public function getInitPosition(): int
{
    return 0;
}
```


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


### Moving items

#### Move

The `move` method allows to move a model to an arbitrary position in the sequence. The positions of another records will be automatically recalculated in response.

```php
$category->move(3);
```


#### Swap

The `swap` method swaps the position of two models.

```php
$category->swap($anotherCategory);
```


#### Arrange

It is also possible to arrange models by their IDs.

The position of each model will be recalculated according to the index of its ID in the given array. 

You can also provide a second argument as a starting position of the records.

```php
Category::arrangeByKeys([3, 5, 7]);
```

### Grouping / Dealing with relations

To allow models to be positioned within groups, you need to override the `newPositionQuery` method.

```php
protected function newPositionQuery()
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

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.


## TODO

- [ ] add possibility to create new models on the beginning of the sequence
- [ ] add support for `move(-1)`, `move(-3)` to move the item at the end of the sequence
- [ ] add `moveToStart()` method
- [ ] add `moveToEnd()` method
- [ ] add `moveUp()` method
- [ ] add `moveDown()` method
- [ ] add `moveAbove($that)` method
- [ ] add `moveBelow($that)` method
- [ ] add `next()` method
- [ ] add `previous()` method
- [ ] add support for quite moving (without `updated_at` overriding)
- [ ] add nova plugin with drag functionality
