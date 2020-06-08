# Modules

Modules are `wrappers` to related components wrapped together in one directory.

# Modules structure

Each module will contain by default the following directories:

- Controllers
  - Admin: **Administration controllers**
  - Site: **Front office site controllers**
- Models: All module **Models**.
- Resources: [Laravel API resoruces](https://laravel.com/docs/5.8/eloquent-resources).
- Repositories: [Module repositories](./repositories).
- routes: 
  - `admin.php` list of admin routes.  
  - `site.php` list of site routes.  

You should add any `Helpers`, `Contracts`, `Exceptions`, `Services` related to that module here as well.

# Configuring routes to work with modules system

All modules are placed in `app/Modules` directory, by default Laravel `RouterProvider` namespaceing the `Controllers` by `App\Http\Controllers` which won't work with our modules structure.

So we need to change that behavior.

Open `app/Providers/RouteServiceProvider.php`

And change `$namespace` property from `App\Http\Controllers` to be only `App` as follows:

`app/Providers/RouteServiceProvider.php`

```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App';
    ...
}
```
# Modifying api.php file

As each module has its own `routes`, we need to slightly change the `routes/api.php` file, add the following code to `routes/api.php`:

`routes/api.php`

```php

// admin
Route::group([
    'prefix' => '/admin',
], function () {    
    // admin routes list
    
    // end of admin routes                            
});

// site routes list

// end of site routes                            
```

> DO NOT REMOVE the comments listed in the previous code as it will help the package to determine where to set the included routes.


The first part of the code is for the admin routes, we **grouped** the admin routes with a `/admin` prefix, you can change it based on your specifications as you want.

The following lines **SHOULD NOT BE** removed from the code for the auto generated routes.

```php

    // admin routes list
    
    // end of admin routes                            

// site routes list

// end of site routes                            
```

# Creating new module   

To create a new module, use `php artisan make:module module-name --data=list,of,data --uploads=list,of,uploads` 

For example, let's create **posts** module:

`php artisan make:module posts --data=title,description --uploads=image`

The previous command will create a posts directory located in `app/Modules/Posts` with the following schema:

```
Laravel Project
└─── app
│   └─── Modules
│       └─── Posts
│           └─── Controllers
│               └─── Admin
│                   └─── PostsController.php
│               └─── Site
│                   └─── PostsController.php
│           └─── Models
│                   └─── Post.php
│           └─── Repositories
│                   └─── PostsRepository.php
│           └─── Resources
│                   └─── Post.php
│           └─── routes
│                   └─── admin.php
│                   └─── site.php
|   
```

Let's see the previous files in more depth.

# Available modules
The following list are included in the package, but you need to run a command to use that module.

Each module has its own installation, features and usage information.

- [Users module](./users-module)

# Admin controller
The `PostsController` in `app/Modules/Posts/Controllers/Admin/PostsController.php` file extends [AdminApiController](./admin-api-controller).

Here is the generated file:

```php
<?php
namespace App\Modules\Posts\Controllers\Admin;

use HZ\Illuminate\Mongez\Managers\AdminApiController; 

class PostsController extends AdminApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => 'posts',
        'listOptions' => [
            'select' => [],
        ],
        'rules' => [
            'all' => [],
            'store' => [],
            'update' => [],
        ],
    ];
}
```

This controller will handle the full `CRUD` functionality as follows:

- index: List records.
- show: Get record.
- Store: Create new record.
- update: Update record.
- destroy: Delete record.

All of the previously mentioned methods, are handled by the base controller, however, you can override these methods to fullfil your needs.

The `AdminApiController` will contract the provided `repository`.

Full details about the `controllerInfo` are detailed in [AdminApiController](./admin-api-controller). 

# Site Controller
As there is a controller to handle the `admin` controller, there is a `site` controller which handle the `public` requests with users/visitors..etc.

The `PostsController` in `app/Modules/Posts/Controllers/Site/PostsController.php` file extends [ApiController](./api-controller).

```php
<?php
namespace App\Modules\Posts\Controllers\Site;

use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\ApiController;

class PostsController extends ApiController
{
    /**
     * Repository name
     * 
     * @var string
     */
    protected $repository = 'posts';

    /**
     * {@inheritDoc}
     */
    public function index(Request $request)
    {
        $options = [];

        return $this->success([
            'records' => $this->repository->list($options),
        ]);
    }
    
    /**
     * {@inheritDoc}
     */
    public function show($id, Request $request)
    {
        return $this->success([
            'record' => $this->repository->get($id),
        ]);
    }
}
```

By default, the `site` controller, will have only two methods the `list` and `show` methods.

The `list` method should return list of records `(posts)` whereas the `show` method returns a single record `(post)`.

The `repository` property indicates what repository to grab the data from.

> Please note that the repository property is declared as a string in `protected $repository = 'posts';` but the [ApiController](./api-controller) will convert it to the the corresponding repository which is `PostsRepository` class.

# Models
The `Models` directory contains only one model which is `Post.php` model.

That model extends one of the following models based on your `database driver configuration`.

- [MYSQL Model Manager](./model-manager).   
- [MongoDB Model Manager](./mongodb-model-manager).

> Both models have their own implementations but we don't use models to insert data, use repositories instead. 

# Repositories
A `PostsRepository` class is created under `Repositories` directory.

The repository in general should be the bridge between any service/controller and database **model(s)**.

It should moderate every query related to database.

For example, getting records based on certain criteria/filters, creating new/updating  records, deleting and so on.  

The Repository extends one of the following repositories based on your `database driver configuration`.

- [MYSQL Repository Manager](./repository-manager)
- [MongoDB Repository Manager](./mongodb-repository-manager)


# Resources

As the accessible data from models should not be all sent, we use resources to modify the data shape that are sent to the api response.

A `Post.php` resource file is created under `Resources` directory.

Check the [Resources Manager](./resources-manager) for more details and options.

# Routes
As we mentioned earlier, there are two files for routes, one for the admin and the othe for the front office site.

Here is how the `app/Modules/Posts/routes/admin.php` file looks like:

```php
<?php

/*
|--------------------------------------------------------------------------
| Posts Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your admin "back office/dashboard" application.
| Please note that this file is auto imported in the main routes file, so it will inherit the main "prefix"
| and "namespace", so don't edit it to add for example "admin" as a prefix. 
*/
Route::group([
    'namespace' => 'Modules\Posts\Controllers\Admin',
    'middleware' => ['logged-in'], // this middleware is used to check if user/admin is logged in
], function () {
    // Restful API CRUD routes 
    Route::apiResource('/posts', 'PostsController');
});
```

A Very simple admin routes, which contain only `ApiResource` routes list.

The only interesting thing here is the `logged-in` middleware.

That middleware is part of the package but optional, check the [Users Module](./users-module) for more details about the available middleware(s).

