# Mongez

This project aims to make using Laravel framework more organized and extensible.

## Table of contents

-   [Mongez](#mongez)
-   [Table of contents](#table-of-contents)
-   [Requirements](#requirements)
-   [Installation](#installation)
-   [Configurations](#configurations)
    -   [Translatable](#translatable)
    -   [Change Log](#change-log)
-   [Documentation](#documentation)

## Requirements

-   Laravel `>=11`

### For compatibility with Laravel 10, please use version 3.0 of this package.

### For compatibility with older versions of Laravel, please use version 2.x of this package.

## Installation

Run the following Command in your cli.

`composer require hassanzohdy/mongez`

## Configurations

Once its done run the following command to create the `config/mongez.php` file.

`php artisan vendor:publish --provider="HZ\Illuminate\Mongez\Providers\MongezServiceProvider"`

## Translatable

```php
<?php
use HZ\Illuminate\Mongez\Translation\Traits\Translatable;

class MyClass
{
  use Translatable;

  public function index()
  {
    $this->transUsers('users.name'); // will translate from `Users` module and `users` file and the keyword is `name
    $this->transUsers('usersGroups.permissions'); // will translate from `Users` module and `usersGroups` file and the keyword is `permissions
  }
}
```

## Change Log

-   3.1.4 (30 Sep 2024)
    -   Add published scope (user will add to models that need it)
    -   Add `findBy`, `forUser` and `forCustomer` scopes to all models
-   3.1.0 (29 Sep 2024)
    -   Update codebase to support laravel 11+
    -   Remove Carbon `setWeekStartsAt` and `setWeekEndsAt` as it was removed from carbon.
-   3.0.1 (29 Sep 2024)
    -   Update codebase to support laravel 10 with new mongodb elquent integration
-   2.24.2 (24 Dec 2023)
    -   Fixed request sql options is overwritten by current class sql options
-   2.24.1 (24 Nov 2022)
    -   Fixed empty array of date in `date_response`.
-   2.24.0 (24 Nov 2022)
    -   Enhanced Resource Manager Errors to display the resource class name.
-   2.23.5 (23 Oct 2022)
    -   Now generated model will have `casts` property instead of `dates` for date casting.
-   2.23.4 (22 Oct 2022)
    -   Fixed defining the class namespace of `UTCDateTime` in `functions.php` file.
-   2.23.3 (22 Oct 2022)
    -   Fixed collectables to return proper array syntax instead of objects.
-   2.22.2 (28 Aug 2022)
    -   Fixed `ARRAYABLE_DATA` on listing as it is encoded to json.
-   2.22.1 (28 Aug 2022)
    -   `RepositoryManager.wrapMany` will return empty array without passing the collection to the resource if teh given array|collection is empty.
-   2.22.0 (28 Aug 2022)
    -   `config/mongez.php` config
    -   Changed `misc` key to `date`.
    -   Changed `CarbonImmutable` to `immutable` under `date` key.
    -   Added `week_starts_at` and defaults to `Saturday`.
    -   Added `week_ends_at` and defaults to `Friday`.
-   2.21.0 (27 Aug 2022)
    -   Added `getPaginationInfo` in the repository manager.
    -   `getPaginateInfo` now is deprecated and will be removed in **V3.0**.
    -   Added `first` method to return the first matched element, takes the same array options as `listModels` and return one model.
-   2.20.0 (27 Aug 2022)
    -   Added `saveActionType` property to the repository, it can be used in `setData`, and its value will depend on the current action, `static::CREATE_ACTION` | `static::UPDATE_ACTION` | `static::PATCH_ACTION`.
-   2.18.0 (17 Aug 2022)
    -   Added `date_response` to format the given date to `timestamp` `humanTime` `text` and `format` outputs.
    -   Added `localized_date` to convert the given date into formatted date based on the locale code.
    -   Used `date_response` in the resource manager to collect dates.
-   2.17.0 (15 Aug 2022)
    -   Added Aggregate Utilities
-   2.16.0 (14 Aug 2022)
    -   Added `date` and `date:between` filters to `FILTER_BY` repository constant.
-   2.15.0 (12 Aug 2022)
    -   Added `carbonImmutable` feature to change the `now` function into immutable carbon instance.
-   2.14.0 (10 Aug 2022)
    -   Added `LOCATION_DATA` constant to resource manager to return proper geo location data.
-   2.12.0 (26 July 2022)
    -   Added `LOCALIZED_COLLECTABLE_DATA` constant to resource manager to localize data that are in array list.
-   2.11.0 (23 July 2022)
    -   Now `WHEN_AVAILABLE` in resource manager if set to `true`, it will strip out any missing value from the model so the resource will only return existing data without any default values for any missing data.
-   2.1.21 (28 Feb 2022)
    -   Fixed Multiple Trait Methods Alias
-   2.1.20 (28 Feb 2022)
    -   Fixed Missing Semi Colon
-   2.1.19 (28 Feb 2022)
    -   Fixed `Model`, `Resource` and `Filter` in the repository while creating child module to receive the child module name instead of the parent module.
-   2.1.17 (28 Feb 2022)
    -   Added `Translatable` trait.
-   2.1.14 (28 Feb 2022)
    -   Fixed generated `database` directory to be `Database`.

## Documentation

See full documentation in the [wiki page](https://github.com/hassanzohdy/mongez/wiki).
