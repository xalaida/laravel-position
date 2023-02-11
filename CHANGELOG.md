# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.5.1 - 2023-02-11

### Added

- Move using negative position values

## [0.5.0] - 2023-02-09

### Added

- Possibility to create model in the middle of the sequence
- Possibility to create model in the beginning of the sequence
- Possibility to update positions without shifting other models
- Extra argument for shift amount in `shiftToStart` and `shiftToEnd` methods

### Changed

- Rename method `getInitPosition` to `startPosition`
- Position of other models now are shifted after the model update

## [0.4.1] - 2023-02-08

### Added

- Possibility to update `position` attribute along with other attributes

## [0.4.0] - 2022-05-08

### Added

- Laravel 9 support

## [0.3.0] - 2021-06-24

### Fixed

- Fix position query scoping for relations

## [0.2.0] - 2021-06-19

### Added

- Documentation
- `OrderByPosition` global scope
- Support for models delete
- `swap` method
- Add PHP 8 support

### Changed

- Rename `arrangeByIds` into `arrangeByKeys`
- Extract `arrangeByKeys` method into query builder
- Extract shift methods into query builder

## [0.1.0] - 2021-06-13

### Added

- Base ordering features
