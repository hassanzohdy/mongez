# Mongez

This project aims to make using Laravel framework more organized and extensible.

# Table of contents

- [Mongez](#mongez)
- [Table of contents](#table-of-contents)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configurations](#configurations)
  - [Translatable](#translatable)
  - [Change Log](#change-log)
- [Documentation](#documentation)

# Requirements

- Laravel `>=5.6`

# Installation

Run the following Command in your cli.

`composer require hassanzohdy/mongez`

# Configurations

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

- 2.18.0 (17 Aug 2022)
  - Added `date_response` to format the given date to `timestamp` `humanTime` `text` and `format` outputs.
  - Added `localized_date` to convert the given date into formatted date based on the locale code.
  - Used `date_response` in the resource manager to collect dates.
- 2.17.0 (15 Aug 2022)
  - Added Aggregate Utilities
- 2.16.0 (14 Aug 2022)
  - Added `date` and `date:between` filters to `FILTER_BY` repository constant.
- 2.15.0 (12 Aug 2022)
  - Added `carbonImmutable` feature to change the `now` function into immutable carbon instance.
- 2.14.0 (10 Aug 2022)
  - Added `LOCATION_DATA` constant to resource manager to return proper geo location data.
- 2.12.0 (26 July 2022)
  - Added `LOCALIZED_COLLECTABLE_DATA` constant to resource manager to localize data that are in array list.
- 2.11.0 (23 July 2022)
  - Now `WHEN_AVAILABLE` in resource manager if set to `true`, it will strip out any missing value from the model so the resource will only return existing data without any default values for any missing data.
- 2.1.21 (28 Feb 2022)
  - Fixed Multiple Trait Methods Alias
- 2.1.20 (28 Feb 2022)
  - Fixed Missing Semi Colon
- 2.1.19 (28 Feb 2022)
  - Fixed `Model`, `Resource` and `Filter` in the repository while creating child module to receive the child module name instead of the parent module.
- 2.1.17 (28 Feb 2022)
  - Added `Translatable` trait.
- 2.1.14 (28 Feb 2022)
  - Fixed generated `database` directory to be `Database`.

# Documentation

See full documentation in the [wiki page](https://github.com/hassanzohdy/mongez/wiki).
