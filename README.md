# Laravel Organizer

This project aims to make using Laravel framework more organized and extensible.

# Table of contents
- [Laravel Organizer](#laravel-organizer)
- [Table of contents](#table-of-contents)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configurations](#configurations)
- [Getting started](#getting-started)
- [Documentation](#documentation)

# Requirements
- Laravel `>=5.6` 

# Installation

Run the following Command in your cli.

`composer require hassanzohdy/laravel-organizer`

# Configurations

Once its done run the following command to create the `config/organizer.php` file.

`php artisan vendor:publish --provider="HZ\Illuminate\Organizer\Providers\ConfigurationsProvider"`

# Getting started
Once the package is fully installed successfully, you will find the following files/directories in your application.

```
Laravel Project
└─── app
│   └─── Contracts
│       └─── --- Interfaces here ---
│   └─── Exceptions
│       └─── --- exceptions here ---
│   └─── Helpers
│   |   └─── --- Helpers Classes And functions  ---
│   └─── Services
│       └─── --- Handling 3d party services such as online payments ---  
│   └─── Macros
│       └─── --- macros here ---
│   └─── Managers
│       └─── --- Abstract classes here ---
│   └─── Models
│       └─── --- Models list here ---
│   └─── Repositories
│       └─── --- Repositories here ---
│   └─── Traits
│       └─── --- Traits here ---
└─── config
|   └─── organizer.php 
```

# Documentation

See full documentation in the [wiki page](https://github.com/hassanzohdy/laravel-organizer/wiki).