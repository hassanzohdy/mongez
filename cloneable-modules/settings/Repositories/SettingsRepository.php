<?php

namespace App\Modules\Settings\Repositories;

use App\Modules\Settings\{
    Models\Setting as Model,
    Resources\Setting as Resource,
    Filters\Setting as Filter
};

use HZ\Illuminate\Mongez\{
    Contracts\Repositories\RepositoryInterface,
    Managers\Database\MongoDB\RepositoryManager
};
use Illuminate\Support\Arr;

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
    const DATA = ['name', 'group', 'type', 'value'];

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
     * Set the columns will be filled with single record of collection data
     * i.e [country => CountryModel::class]
     * 
     * @const array
     */
    const DOCUMENT_DATA = [];

    /**
     * Set the columns will be filled with array of records.
     * i.e [tags => TagModel::class]
     * 
     * @const array
     */
    const MULTI_DOCUMENTS_DATA = [];

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
    const PAGINATE = false;

    /**
     * Number of items per page in pagination
     * If set to null, then it will taken from pagination configurations
     * 
     * @const int|null
     */
    const ITEMS_PER_PAGE = null;

    /**
     * If set to true, then the file will be stored as its uploaded name
     * 
     * @const bool
     */
    const UPLOADS_KEEP_FILE_NAME = true;

    /**
     * Loaded Settings
     * 
     * @var array
     */
    protected $loadedSettings = [];

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
    }

    /**
     * Map the value of the given setting
     * 
     * @param array $setting
     * @param Model $settingModel
     * @return mixed  
     */
    protected function mapValue(array $setting, $settingModel)
    {
        switch ($setting['type']) {
            case 'file':
                // check the value from the setting array
                // if no value inside it, return the value from settings model
            case 'intArray':
                return array_map('intval', $setting['value']);
            case 'floatArray':
            case 'doubleArray':
                return array_map('floatval', $setting['value']);
            case 'int':
                return (int) $setting['value'];
            case 'float':
            case 'double':
                return (float) $setting['value'];
            case 'bool':
            case 'boolean':
                return (bool) $setting['value'];
            case 'text':
            case 'string':
                return (string) $setting['value'];
            default:
                return $setting['value'];
        }
    }

    /**
     * A shorthand method to get the value of order taxes
     * 
     * @return float
     */
    public function getOrderTaxes(): float
    {
        return (float) $this->getSetting('general', 'orderTaxesValue');
    }

    /**
     * Set the given setting 
     * 
     * @param array $setting
     * @return void
     */
    public function set(array $setting)
    {
        $settingModel = Model::where('group', $setting['group'])
            ->where('name', $setting['name'])
            ->first();

        if (!$settingModel) {
            $settingModel = new Model();
        }

        $this->updateModel($settingModel, [
            'group' => $setting['group'],
            'name' => $setting['name'],
            'value' => $this->mapValue($setting, $settingModel),
            'type' => $setting['type'],
        ]);
    }

    /**
     * Load the given settings groups internally to be used later with getValue Method
     * 
     * @param ...string $group
     * @return array
     */
    public function load(...$group)
    {
        $settings = Model::whereIn('group', $group)->get();

        foreach ($settings as $setting) {
            $this->loadedSettings[$setting->name] = $setting->value;
        }

        return $this->loadedSettings;
    }

    /**
     * Load the given settings for the given groups with records untouched
     * 
     * @param ...string $group
     * @return Collection
     */
    public function listByGroup(...$group)
    {
        return Model::whereIn('group', $group)->get();
    }

    /**
     * Get value from loaded settings
     * 
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function getValue($name, $default = null)
    {
        return Arr::get($this->loadedSettings, $name, $default);
    }

    /**
     * Get the value for the given key directly
     * 
     * @param string $key
     * @return mixed
     */
    public function getSetting($group, $key)
    {
        return Model::where('group', $group)->where('name', $key)->value('value');
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
}