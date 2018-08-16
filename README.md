# Laravel Startup

This project aims to make using Laravel framework more organized and extensible.

# Table of contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Database](#database)
- [Models](#models)
- [Repositories](#repositories)
- [Macros](#macros)
- [traits](#traits)
- [Items](#items)
- [Contracts](#contracts)
- [Managers](#managers)
- [Helpers](#helpers)

# Requirements
- Laravel `>=5.6` 

# Installation

Run the following Command in your cli.

`composer require hassanzohdy/laravel-startup`

Once its done run the following command

`php vendor/hassanzohdy/laravel-startup/laravel-startup`

Last step is to add the following service provider in `config/app.php` providers list.

`App\Providers\StartupServiceProvider::class`

If you want to load `app/helpers/functions` automatically add it in your `composer.json` in the autoload section so it look like:

```json
"autoload": {
    "files": [
        "app/Helpers/functions.php"
    ]
```

then run `composer dump-autoload`.