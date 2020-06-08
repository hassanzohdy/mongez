# API controller

API controller of every generated module extends the  API controller of mongez package.

Once you generate new module the mongez generate API controller to you. 

The module API controller located at 
`App\Modules\ModuleName\Controllers\Site`

In API controller we have a property with repository variable

```php
namespace App\Modules\ModuleName\Controllers\Site;

use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\ApiController;

class ModuleNameController extends ApiController
{
    /**
     * Repository name
     * 
     * @var string
     */
    CONST REPOSITORY_NAME = 'repoName';
}
```
By default we set the repoName with the module repository name.

## The requests that API controller handles
1. [List of records](#frontList) 
2. [Get single record](#frontSingle)

### <a name="frontList"></a> List of records

Request end-point
`/api/moduleName`

Request method
`GET`

Handling logic 
```php
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
```

By default mongez generate `index` function that returns objects from repository `list` function that return all repository records.

`Request response`
```json
{
    "records" : [
        {

        },
        {

        }
    ]
}
```

### <a name="frontSingle"></a> Get single record.

Request end-point
`/api/moduleName/{id}`

Request method
`GET`

Handling logic 
```php
    /**
     * {@inheritDoc}
     */
    public function show($id, Request $request)
    {
        return $this->success([
            'record' => $this->repository->get($id),
        ]);
    }
```

By default mongez generate `show` function that returns object from repository `get` function that return single record by id.


`Request response`
```json
{
    "record" : {

    }
}
```

`Note` You can set what data you want to return in module `resource` file.
 