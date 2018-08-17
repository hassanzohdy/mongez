# Laravel Organizer

This project aims to make using Laravel framework more organized and extensible.

# Table of contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Getting Started](#getting-started)
- [Contracts](#contracts)
- [Macros](#macros)
- [Managers](#managers)
- [traits](#traits)
- [Items](#items)
- [Repositories](#repositories)
- [Database](#database)
- [Helpers](#helpers)

# Requirements
- Laravel `>=5.6` 

# Installation

Run the following Command in your cli.

`composer require hassanzohdy/laravel-organizer`

Once its done run the following command

`php vendor/hassanzohdy/laravel-organizer/organize`

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
│   └─── Items
│       └─── --- items ---  
│   └─── Macros
│       └─── --- macros here ---
│   └─── Managers
│       └─── --- Abstract classes here ---
│   └─── Repositories
│       └─── --- Repositories here ---
│   └─── Traits
│       └─── --- Traits here ---
└─── config
|   └─── organizer.php 
```

# Contracts

Contracts are list of `interfaces` that will be used to store all of your interfaces in that directory so you can easily access all interfaces from one directory. 

## Available contracts
- [RepositoryInterface](./docs/contracts/repository)

# Macros

Macros are used to extend `append` methods to existing classes on the runtime without modifying the original class.

All macros should be placed in `app/Macros` directory.

# How to add new macro

Go to `config/organizer.php` and in the `macors` section add your macro as the key will be the original class and the value will be your `mixin` class as follows:

```php
'macros' => [
    Illuminate\Support\Collection::class => HZ\Laravel\Organizer\App\Macros\Support\Collection::class
]
```

So lets take the `Collection` class for example, let's assume we want to add more methods to the `Illuminate\Support\Collection` class like `wake` method.

This method mainly applies a callback on the entire collection without the need of creating new one, which basically using the [array_walk() ](http://php.net/manual/en/function.array-walk.php) function.

Let's assume we've a price list 

`$priceList = collect([100, 200, 900, 500]);`

Now let's get the same price list but with `10%` taxes added on it.

```php
$priceList->walk(function (& $price) {
    $price += $price * 0.1;
});
```

## Available Macros
- [Illuminate\Support\Collection](./docs/macros/collection) 
- [Illuminate\Support\Str](./docs/macros/str) 
- [Illuminate\Support\Arr](./docs/macros/arr) 
- [Illuminate\Database\Schema\Blueprint](./docs/macros/blueprint) 

# Managers

Managers are `abstract classes` that will be used for `inheritance` only.

Managers should be used to **encapsulate** common methods between same classes like `RepositoryManager` for instance, it's used to implement some common methods between all [repositories](#repositories) and also add some `abstract` method for the child classes.

## Available Managers
- [Item](./docs/managers/item)
- [RepositoryManager](./docs/managers/repository)
- [ApiController](./docs/managers/api)


# Traits

Traits are massively good for using set of methods in many classes to fullfil the drop of the php single class inheritance.

Any traits should be placed in the `app/Traits` directory 

## Available traits
- [RepositoryTrait](./docs/traits/repository)

# Items
Every item is just a `Fluent` class that is used to dynamically set/get data in the class.

The main purpose for items here is to manage what type of data that you want to send in `json` responses as you may not want to send all fetch columns from the table or even modify some values.

Let's assume we want to list of users that has the following columns in the `users` table:

`id`, `first_name`, `last_name`, `email`, `password`, `image`

For example let's say we want the response of each user be like:

```json
{
    "id": 41,
    "email": "john.doe@example.com",
    "image": "https://mysite.com/public/images/john-doe.jpg",
    "name": {
        "first": "John",
        "last": "Doe",
        "full": "John Doe"
    }
}
```

So we need to do two things here, adjusting the `name` property and setting a full `image url` path for each user.

So our `app/Items/User/User.php` class should look like:

```php

namespace App\Items\User;

use App\Managers\Item;

class User extends Item 
{
    /**
     * Set the data that should be sent in response
     * 
     * @return array
     */
    public function send(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'image' => url('images/' . $this->image),
            'name' => [
                'first' => $this->first_name,
                'last' => $this->last_name,
                'full' => $this->first_name . ' ' . $this->last_name,
            ],
        ];
    }  
}
```

Also peer in mind that `Items` should be used in [repositories](#repositories) in the `list` method.

# Repositories
# Database
# Helpers

Helpers are groups of `classes` and helper `functions`.

Mainly, helpers are used to do some functions for other classes.

For example, the [Select](./docs/helpers/select) is used to manage any passed selections to any [Repository](#repositories).

Sometimes you want to do some quick function for easy access, for example in the [Helper functions](./docs/helpers/functions) file we have the [user()](./docs/helpers/functions#user) function which returns the object of the current user.

Best way to inject your `functions` files is to add it in your `composer.json` file in the `autoload` section, like this:

```json
"autoload": {
    "files": [
        "app/Helpers/my-functions.php"
    ]
}
```

> Don't forget to run `composer dump-autoload` after adding the helper functions.