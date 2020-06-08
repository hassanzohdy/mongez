# Database

There are some few features is added in this package to be build a better application.

- [Database](#database)
- [Query builder](#query-builder)
- [Migrations](#migrations)
- [Mysql key was too long error](#mysql-key-was-too-long-error)
- [Models](#models)
- [Example](#example)

# Query builder

As the database query builder being `macroable`, we've add some useful macros in the [Macro Query Builder](./macro-query).

# Migrations
Migrations is a powerful tool that enables you manipulate database schema without the need of using some GUI 3d parties applications like `phpMyAdmin`.

In this package, we added a little helper in the [Database table builder](./macro-blueprint) the `loggers` section which will create some database fields for the table to be used for tracking actions for this table like who update/created/deleted the record.

# Mysql key was too long error
When you create new schema table using `migrations` in laravel, you may face the following error:

> SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 767 bytes (SQL: alter table users add unique users_email_unique(email))

which occurs if you're using an older version of MYSQL than `v5.7.7`.

So there is a fix for it by defining the **default string length** which will make you set the maximum allowed length for you.

This has a simple fix which is implemented here.

Go to `config/mongez.php` you will find in the beginning the following:

```php
    'database' => [
        'mysql' => [
            'defaultStringLength' => 191,
        ],
    ],
```

This will automatically fix it for you.

> If you want to disable it, set the value of `defaultStringLength` to zero.

Read more about it from [here](https://laravel-news.com/laravel-5-4-key-too-long-error).

# Models

Models are part of [Modules](./modules), they basically manage the database tables.

Once you've created the table based on the [Blueprint loggers structure](#macro-blueprint), you can now `extend` the provided model in this package to automatically log any action for every record. 

All you've to do is just to extend `HZ\Illuminate\Mongez\Managers\Database\MYSQL\Model` class instead of `Illuminate\Database\Eloquent\Model` as the model will automatically extend the base model.

> Please note that all models here use softDeletes for deleting records.

So now any model will extend our model it will automatically add the id of the user who created/updated/deleted it and the time of the action.


# Example

`MyModel.php`

```php

namespace App\Models;

use HZ\Illuminate\Mongez\Managers\Database\MYSQL\Model;

class MyModel extends Model
{
    //
}
```

Now let's call it from some controller.

`MyController.php`

```php

/**
 * {@inheritDoc}
 */ 
public function store(Request $request) 
{
    $myModel = new MyModel;

    $myModel->name = 'Hasan';
    $myModel->email = 'hassanzohdy@gmail.com';

    $myModel->save();
}
```

Once you called the `save` method, the created_at/by and updated_at/by will automatically be filled by the current user id using the [user()](./helper-functions#user) to get the current user id and for the timestamp it will be the default behavior of the laravel model.  

> You can override the user id by overriding the `byUser` in the model 

```php
class User extends Model
{
    
    /**
     * Get user by id that will be used with created by, updated by and deleted by
     * 
     * @return int
     */
    protected function byUser()
    {
        return user()->id ?? 0;
    } 
}
```

For more information, please check [ModelTrait](./model-trait) and [ModelManager](./model-manager).