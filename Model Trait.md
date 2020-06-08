# ModelTrait
This trait is mainly used to add the `loggers` based on current writing operation.

# Usage
Just add this trait to any model you want to have the `*_by` loggers automatically handled. 

> Please note that all models here use `softDeletes` for deleting records as loggers work only with `softDeletes`.

# Example
In your `app/User.php`, include the trait in the beginning of it.

`appUser.php`

```php
<?php

namespace App;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use HZ\Illuminate\Mongez\Traits\ModelTrait;

class User extends Authenticatable
{
    use Notifiable, ModelTrait;
}
```

Now any time a user is created|updated|deleted the value of the `created_by|updated_by|deleted_by` will automatically filled with the current user id using the [user()](./Helper-Functions#user) function.

# Example

Now let's call it from some controller.

MyController.php

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
# Customizing the *_by fields names

If you're using a different name for any of the *_by family, feel free to modify it based on your needs.


```php
<?php

namespace App;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use HZ\Illuminate\Mongez\Traits\ModelTrait;

class User extends Authenticatable
{
    use Notifiable, ModelTrait;

    /**
     * Created By column
     * Set it to false if this column doesn't exist in the table
     *
     * @const string|bool
     */
    const CREATED_BY = 'created_by';

    /**
     * Updated By column
     * Set it to false if this column doesn't exist in the table
     *
     * @const string|bool
     */
    const UPDATED_BY = 'updated_by';

    /**
     * Deleted By column
     * Set it to false if this column doesn't exist in the table
     *
     * @const string|bool
     */
    const DELETED_BY = 'deleted_by';
}
```


# Model table name

Sometimes you want to get the **Model** table name.

Now you can achieve this by calling the static method `getTableName` directly from the model

```php
echo User::getTableName(); // users
```

# Model Manager
This trait is used in the [Model Manager](./Model-Manager) so you can easily extend the manager instead of the base `Eloquent` model. 
