# Mongez Documentation

- [Mongez Documentation](#mongez-documentation)
- [Requirements](#requirements)
- [Installation](#installation)
- [Why Mongez](#why-mongez)
- [Configurations](#configurations)
- [Packages to install with](#packages-to-install-with)
- [Mongez command lines](#mongez-command-lines)
- [Creating a repository file into target module](#creating-a-repository-file-into-target-module)


# Requirements
- Laravel `>=5.6` 

# Installation

Run the following Command in your cli.

`composer require hassanzohdy/mongez`

# Why Mongez

This package massively relies on only APIs backend as it doesn't support any kind of `views`

The package provides the following features to your project within the CLI interface:

- Everything is modulus, we encapsulate every module together.
- This will increase up the productivity of your development.
- It supports `MYSQL` and `MongoDB` databases.
- You don't care much about creating the `CRUD` system, we do this for you.
- `Repositories`, `Contracts` **Interfaces**, `Exceptions`, `Models` and `Resources` are there for each module to be used!
- [Macros](./macros) are now very easy to be created.     
- Many many helper functions and classes to inherit from are there.
- Less code are here, you'll probably treat the application as a configuration more than a code.

# Configurations

Once its done run the following command to create the `config/mongez.php` file.

`php artisan vendor:publish --provider="HZ\Illuminate\Mongez\Providers\MongezServiceProvider"`

> You must make one change into `App\Providers\RouteServiceProvider.php` the `$namespace` property value must be like that 
```php
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App';
```

> Each part of the package has its own configurations, so you'll find the corresponding documentation based on its location of the wiki pages.

See [Rest of configurations](./configurations) for more details.

# Packages to install with

As mentioned earlier, this package mostly for APIs backends, so you would need to take care of `cors`, luckily [there is a laravel package for this matter](https://github.com/barryvdh/laravel-cors).


If you're going to use mongodb then don't forget to install the [`Laravel mongodb driver handler`](https://github.com/jenssegers/laravel-mongodb).
 
# Mongez command lines

Mongez provide every feature of it in command line interface to speed up your development.

`Here are list of command lines`

- [Creating Module](#engezModule)
- [Creating Resource](#engezResource)
- [Creating Repository](#engezRepository)
- [Creating Model](#engezModel)
- [Creating Contoller](#engezController)
- [Creating Migration](#engezMigration)
- [Creating Migrate](#engezMigrate)
- [Clone Module](#cloneModule)

> Creating a module is simple and easy. Run the following command to create a module.

``` 
php artisan engez:module <module-name>
```

There are many command options for [create module](./EngezModule) for more details.

> Creating a resource file into target module

``` 
php artisan engez:resource <resource-name> <module-name>
```

For more details [create resource](./EngezResource).


# Creating a repository file into target module

``` 
php artisan engez:repository <repository-name> <module-name>
```

For more details [create repository](./EngezRepository).


> Creating a model file into target module

``` 
php artisan engez:model <model-name> <module-name>
```

For more details [create model](./EngezModel).


> Creating a model file into target module

``` 
php artisan engez:model <model-name> <module-name>
```

For more details [create model](./EngezModel).

> Creating a controller file into target module

``` 
php artisan engez:controller <controller-name> <module-name>
```

For more details [create controller](./EngezController).

> Creating a migration file into target module

``` 
php artisan engez:migration <migration-name> <module-name>
```

For more details [create migration](./EngezController).

> Run the database migrate to target modules

``` 
php artisan engez:migrate modules = list of modules
```

For more details [run migrate](./EngezController).

> Copying pre exist module in your projects.
in this time there is a only ``users`` module that you can clone in your project

``` 
php artisan clone:module <module-name>
```

Copying pre exist module in your projects.
in this time there is a only ``users`` module that you can clone in your project

``` 
php artisan clone:module <module-name>
```

Replace ```<module-name>``` with one of pre existing modules

By default mongez has a user module as pre existing module you can use.

For more details [user module](./WhatYouGetInUsersModule).
