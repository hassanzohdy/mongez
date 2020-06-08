## What you get in generated modules

## <a name="moduleStructure"> Module structure </a>

<div class="highlight highlight-html">
<pre>
app/
Modules/
  ├── module-name /
      ├── <a href="s"> Controllers</a>/
            ├── Admin/
                ├── <strong>module-name</strong>Controller.php
            ├── Site/
                ├── <strong>module-name</strong>Controller.php
      ├── <a> database </a>/
            ├── migrations/
                |- create_<strong>module-name</strong>_table.php 
      ├── <a> docs </a>/
            |- <strong>module-name</strong>.postman.json
            |- README.md
      ├── <a> Models </a>/
            |- <strong>module-name</strong>.php
      ├── <a> Providers </a>/
            |- <strong>module-name</strong>ServiceProvider.php
      ├── <a> Repositories </a>/
            |- <strong>module-name</strong>Repository.php
      ├── <a> Resources </a>/
            |- <strong>module-name</strong>.php
      ├── <a> routes </a>/
            |- admin.php
            |- site.php
        </pre>
</div>

## What you get in controller folder
- **[Controller for admin](#adminController)**
- **[Controller for front](#siteController)**

### <a name="adminController">What you get in controller of admin</a>

In controller of admin site we provide to you resource routes of module.

``` php

namespace App\Modules\moduleName\Controllers\Admin;

use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\AdminApiController; 

class moduleController extends AdminApiController
{
    /**
     * Controller info
     *
     * @var array
     */
    protected $controllerInfo = [
        'repository' => 'moduleRepositoryName', // this value is auto filled within generating of module
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

in ``select`` key of listOptions you can set all fields you want to retrieve of this model.

in ``filterBy`` key of listOptions you can set all fields that this model can be filtered by.

in ``paginate`` key of listOptions you can configure the paginate by default it will be null.

in ``rules`` key you can set all your validation based on your need.

you will set common validation in ``all`` key and others validation rules you set it based on your ``store`` and ``update`` operation.


### <a name"sitController"> What you get in controller of front </a>

``` php

namespace App\Modules\ModuleName\Controllers\Site;

use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\ApiController;

class ModuleController extends ApiController
{
    /**
     * Repository name
     * 
     * @var string
     */
    protected $repository = 'repoName';

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

In front site of module we provide two end-points one for listing and other for the get single record.


## What you get in database folder

In database folder you will get the create migration file with your options of module data.

```Note``` you will get folder with schema file of module if your database driver is mongo database. 

## What you get in docs folder 

We generate for you ```postman file``` with your entered module options and with environment variables.

and you will get the ```Readme.md``` with basic info of module.

## What you get in models folder

Your module model is same laravel generated model.

## What you get in providers folder

We trait the module as a service and in this folder you will find module-nameServiceProvider file that boot the module routes

## What you get in repository folder

The repository design pattern is a powerful strategy to map your data models and make it easier.

Repositories are your database handler for each module | section of your application.

For more details about your (module repository)[./Repository]

## What you get in your resource folder 

When building an API, we want layer to transform between the model and JSON response, Resource allows you to control the returned data from module model to API.

For more details about your (module resource)[./Resource]

## What you get in your routes folder

You will get ``admin`` file with resource routes and you can add your module middlewareList.

``site`` file with the two end-points one for listing and one for get single record.

For more details about (admin routes)[./AdminApiController]

For more details about (site routes)[./ApiController]
