# ModelManager

The `ModelManager` basically extends the `Eloquent` model which makes won't make any conflict with your current code.

# Fillable fields

As [Repositories](./repositories) in this package is responsible for handling insertions and updates, this `Model` class will disable the `filled` attributes automatically so you won't need to set your `$filled` attributes. However, you can override this behavior by setting the value of `protected $guarded` in your model to whatever values you want.

What actually happens here is the `$guarded` property is set to be empty so there are no guarded fields.

> You must be aware when creating or updating your models and DO NOT forget to validate any data in your controller.

# Model Trait

The [ModelTrait](./model-trait) is injected here so any model that extends this model will automatically have the advantage of filling the `*_by` loggers automatically.


`User.php`
```php
<?php

namespace App;

use HZ\Illuminate\Mongez\Managers\Database\MYSQL;

class User extends Model
{
}
```

# Get model info

To get all information returned from the database, use the `info` method.

```php
$user = User::find(531);

dd($user->info()); // array of info for the user record. 

```

# MongoDB Model Manager

This model works with `MYSQL` driver, but there's another `Model Manager` that moderates the models for `MongoDB` driver.

[You can check it from here](./mongodb-model-manager).