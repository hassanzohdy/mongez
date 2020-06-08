# Admin Api controller

Admin API controller of every generated module extends the [AdminApiController](AdminApiController) base controller that extends from [API Controller](#ApiController.md)

Once you generate new module the mongez generate Admin API controller to you. 

The module admin API controller located at 
`App\Modules\ModuleName\Controllers\Admin`


```php
namespace App\Modules\ModuleName\Controllers\Admin;

use HZ\Illuminate\Mongez\Managers\AdminApiController; 

class ModuleNameController extends AdminApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => repoName,
        'listOptions' => [
            'select' => [],
            'filterBy' => [],
            'paginate' => null, // if set null, it will be automated based on repository configuration option
        ],
        'rules' => [
            'all' => [],
            'store' => [],
            'update' => [],
        ],
    ];
}
```

The first thing you can see in admin API controller controllerInfo property you find the repository and auto filled with module repository.

You can define returned data from repository in select.
You can define all filtered options.
You can active the paginate from the controller info or you can do it from module repository.

In rules you can define all rules you want
in `all` you set rules that validate on `store|update` actions.
in `store` you set rules that validate on `store` action.
in `update` you set rules that validate on `update` action.


## The requests that Admin API controller handles
1. [List of records](#backList) 
2. [Create new record](#backSingle)
3. [Update record](#backSingle)
4. [Delete record](#backtSingle)
5. [Get single record](#backSingle)

All Handling logic of these requests exits in AdminApiController base class.
