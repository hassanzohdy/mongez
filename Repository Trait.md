# RepositoryTrait

This trait is used to dynamically use [Repositories](./repositories.md).

This trait could be used in controllers to use repositories easily .

# Usage

To use the trait just include it in your class and use the repository based on its name. 

# Example
In your `app/Http/Controllers/Controller.php`, include the trait in the beginning of it.


`app/Http/Controllers/Controller.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use HZ\Illuminate\Mongez\Traits\RepositoryTrait;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, RepositoryTrait;
}
```

Now in any of your controllers, just call the repository base on its name in the `config/mongez.php` in the macros section.

`MyController.php`

```php
<?php
namespace App\Http\Controllers;

class MyController extends Controller
{   
    /**
     * Home page
     * 
     * @return string
     */
    public function index()
    {
        $users = $this->users->list([
            'select' => ['id', 'name', 'email'],
            'orderBy' => ['name', 'DESC'],
        ]);

        pred($users);
    }
}
```

If you're going to use the repository trait in many places, just add an alias to it in the `config/app.php` aliases section like this.

```php

    'aliases' => [
        'RepositoryTrait' => HZ\Illuminate\Mongez\Traits\RepositoryTrait::class,
```

Now you can use it directly like this:


`app/Http/Controllers/Controller.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, \RepositoryTrait;
}
```
Or if you want to add all of your classes before the class itself:


`app/Http/Controllers/Controller.php`

```php
<?php

namespace App\Http\Controllers;

use RepositoryTrait;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, RepositoryTrait;
}
```
