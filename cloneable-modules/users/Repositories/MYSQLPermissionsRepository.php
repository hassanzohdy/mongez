<?php
namespace App\Modules\Users\Repositories;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Modules\Users\{
    Models\Permission as Model,
    Resources\Permission as Resource
};

use HZ\Illuminate\Mongez\{
    Contracts\Repositories\RepositoryInterface,
    Managers\Database\MYSQL\RepositoryManager
};

class permissionsRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'permissions';
    
    /**
     * {@inheritDoc}
     */
    const MODEL = Model::class;

    /**
     * {@inheritDoc}
     */
    const RESOURCE = Resource::class;

    /**
     * Set the columns of the data that will be auto filled in the model
     * 
     * @const array
     */
    const DATA = ['name', 'route', 'key', 'type'];       
    
    /**
     * Auto save uploads in this list
     * If it's an indexed array, in that case the request key will be as database column name
     * If it's associated array, the key will be request key and the value will be the database column name 
     * 
     * @const array
     */
    const UPLOADS = [];       
    
    /**
     * Auto fill the following columns as arrays directly from the request
     * It will encoded and stored as `JSON` format, 
     * it will be also auto decoded on any database retrieval either from `list` or `get` methods
     * 
     * @const array
     */
    const ARRAYBLE_DATA = [];

    /**
     * Set columns list of integers values.
     * 
     * @cont array  
     */
    const INTEGER_DATA = [];

    /**
     * Set columns list of float values.
     * 
     * @cont array  
     */
    const FLOAT_DATA = [];

    /**
     * Set columns of booleans data type.
     * 
     * @cont array  
     */
    const BOOLEAN_DATA = [];
    
    /**
     * Add the column if and only if the value is passed in the request.
     * 
     * @cont array  
     */
    const WHEN_AVAILABLE_DATA = [];

    /**
     * Filter by columns used with `list` method only
     * 
     * @const array
     */
    const FILTER_BY = [];

    /**
     * Determine wether to use pagination in the `list` method
     * if set null, it will depend on pagination configurations
     * 
     * @const bool
     */
    const PAGINATE = null;

    /**
     * Number of items per page in pagination
     * If set to null, then it will taken from pagination configurations
     * 
     * @const int|null
     */
    const ITEMS_PER_PAGE = null;
    
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
     * Insert module permissions
     * 
     * @var string $moduleName
     */
    public function insertModulePermissions($moduleName)
    {
        $modelName = strtolower(Str::singular($moduleName));
        $routeName = strtolower($moduleName);

        $modulePermissions = [
            [
                'name' => 'Create new ' .$modelName,
                'route' => '/api/admin/' .$routeName,
                'type' => 'create',
                'key' => $routeName. '.store'
            ],
            [
                'name' => 'Update ' .$modelName,
                'route' => '/api/admin/' .$routeName .'/{' .$modelName .'}',
                'type' => 'update',
                'key' => $routeName .'.update'
            ],
            [
                'name' => 'Get ' .$modelName,
                'route' => '/api/admin/' .$routeName .'/{' .$modelName .'}',
                'type' => 'show',
                'key' => $routeName .'.show'
            ],
            [
                'name' => 'Delete ' .$modelName,
                'route' => '/api/admin/' .$routeName .'/{' .$modelName .'}',
                'type' => 'delete',
                'key' => $routeName .'.destroy'
            ],
            [
                'name' => 'List of ' .$routeName,
                'route' => '/api/admin/' .$routeName,
                'type' => 'list',
                'key' => $routeName .'.index'
            ]
        ];

        foreach ($modulePermissions as $modulePermission) {
            $request = new Request;
            $request->replace($modulePermission);
            $this->create($request);
        }
    }
}