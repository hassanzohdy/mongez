# What is a repository 
The repository design pattern is a powerful strategy to map your data models and make it easier.

Repositories are your database handler for each module | section of your application.

# What you get in repository file
 
```php
namespace App\Modules\moduleName\Repositories;

use App\Modules\Test\{
    Models\moduleName as Model,
    Resources\moduleName as Resource
};

use HZ\Illuminate\Mongez\{
    Contracts\Repositories\RepositoryInterface,
    Managers\Database\MongoDB\RepositoryManager
};

class moduleNameRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'repoName';
}
```

In `NAME` constant mongez auto filled repository name.

```php
    /**
     * {@inheritDoc}
     */
    const MODEL = Model::class;
```

In `MODEL` constant mongez auto filled module model.

```php
    /**
     * {@inheritDoc}
     */
    const RESOURCE = Resource::class;
```

In `RESOURCE` constant mongez auto filled with module handling resource.


```php
    /**
     * Set the columns of the data that will be auto filled in the model
     * 
     * @const array
     */
    const DATA = [];
```

in create command of module we have a several command options one of them `data` option that you fill all string data input

In `DATA` constant mongez auto filled with command option `data` value.

```php
    /**
     * Auto save uploads in this list
     * If it's an indexed array, in that case the request key will be as database column name
     * If it's associated array, the key will be request key and the value will be the database column name 
     * 
     * @const array
     */
    const UPLOADS = [];
```

in create command of module we have a several command options one of them `uploads` option that you fill all string data input

In `UPLOADS` constant mongez auto filled with command option `UPLOADS` value.

```php
    /**
     * Auto fill the following columns as arrays directly from the request
     * It will encoded and stored as `JSON` format, 
     * it will be also auto decoded on any database retrieval either from `list` or `get` methods
     * 
     * @const array
     */
    const ARRAYBLE_DATA = [];
```
In `ARRAYBLE_DATA` set all keys that their values will be array.

```php
    /**
     * Set columns list of integers values.
     * 
     * @cont array  
     */
    const INTEGER_DATA = [];
```
in create command of module we have a several command options one of them `int` option that you fill all string data input

In `INTEGER_DATA` constant mongez auto filled with command option `int` value.

```php
    /**
     * Set columns list of float values.
     * 
     * @cont array  
     */
    const FLOAT_DATA = [FLOAT_LIST];
```
in create command of module we have a several command options one of them `double` option that you fill all string data input

In `FLOAT_DATA` constant mongez auto filled with command option `double` value.


```php
    /**
     * Set columns of booleans data type.
     * 
     * @cont array  
     */
    const BOOLEAN_DATA = [];
```
in create command of module we have a several command options one of them `bool` option that you fill all string data input

In `BOOLEAN_DATA` constant mongez auto filled with command option `bool` value.


```php
    /**
     * Add the column if and only if the value is passed in the request.
     * 
     * @cont array  
     */
    const WHEN_AVAILABLE_DATA = [];
```
In `WHEN_AVAILABLE_DATA` Add all data that deal if their value passed in the request.

```php
    /**
     * Filter by columns used with `list` method only
     * 
     * @const array
     */
    const FILTER_BY = [];
```
In `FILTER_BY` set all data will be filtered by

```php
    /**
     * Determine wether to use pagination in the `list` method
     * if set null, it will depend on pagination configurations
     * 
     * @const bool
     */
    const PAGINATE = null;
```
In `PAGINATE` accept `true|false`.

```php
    /**
     * Number of items per page in pagination
     * If set to null, then it will taken from pagination configurations
     * 
     * @const int|null
     */
    const ITEMS_PER_PAGE = null;
```

In `ITEMS_PER_PAGE` accept number of items.

#### Extra option for mongodb

```php    
    /**
     * Set the columns will be filled with single record of collection data
     * i.e [country => CountryModel::class]
     * 
     * @const array
     */
    const DOCUMENT_DATA = [];
```
In `DOCUMENT_DATA` you set all keys with their model value. 

### Functions

```php
    /**
     * Set any extra data or columns that need more customizations
     * Please note this method is triggered on create or update call
     * 
     * @param   mixed $model
     * @param   \Illuminate\Http\Request $request
     * @return  void
     */
    protected function setData($model, $request) 
    {
        // 
    }

    
    /**
     * Manage Selected Columns
     *
     * @return void
     */
    protected function select()
    {
        //
    }

    
    /**
     * Do any extra filtration here
     * 
     * @return  void
     */
    protected function filter() 
    {
        // 
    }

    /**
     * Get a specific record with full details
     * 
     * @param  int id
     * @return mixed
     */
    public function get(int $id) 
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function onSave($model, $request)
    {
        // 
    }
```