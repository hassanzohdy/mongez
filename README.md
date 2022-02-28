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

- 2.1.17 (28 Feb 2022)
  - Added `Translatable` trait.
- 2.1.14 (28 Feb 2022)
  - Fixed generated `database` directory to be `Database`.

# Documentation

See full documentation in the [wiki page](https://github.com/hassanzohdy/mongez/wiki).