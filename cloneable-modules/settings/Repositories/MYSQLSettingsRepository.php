<?php
namespace App\Modules\Settings\Repositories;

use App\Modules\Settings\{
    Models\Setting as Model,
    Resources\Setting as Resource,
    Filters\Setting as Filter
};

use HZ\Illuminate\Mongez\{
    Contracts\Repositories\RepositoryInterface,
    Managers\Database\MYSQL\RepositoryManager
};

class settingsRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'settings';
    
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
    const DATA = [
        'name', 
        'group', 
        'type',
        'value'
    ];       
    
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
     * Set all filter class you will use in this module
     * 
     * @const array 
     */
    const FILTERS = [
        Filter::class
    ];
    
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
        if ($request->type == 'file') {
            $this->upload($model, $request, ['value']);
        }
        
        if ($request->type == 'bool') {
            $model->value = (bool) $request->value;
        }

        if ($request->type == 'int') {
            $model->value = (int) $request->value;
        }
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
        if (static::USING_CACHE) return $this->wrap($this->getCache((int) $id));
        return Model::find($id);
    }
}