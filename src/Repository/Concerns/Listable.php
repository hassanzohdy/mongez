<?php

namespace HZ\Illuminate\Mongez\Repository\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use HZ\Illuminate\Mongez\Repository\Select;
use Illuminate\Http\Resources\Json\JsonResource;
use HZ\Illuminate\Mongez\Database\Filters\FilterManager;

trait Listable
{
    /**
     * Select Helper Object
     *
     * @var \HZ\Illuminate\Mongez\Helpers\Repository\Select
     */
    protected $select;

    /**
     * Options list
     *
     * @param array
     */
    protected $options = [];

    /**
     * Pagination info
     *
     * @var array
     */
    protected $paginationInfo = [];

    /**
     * Current used resource class, 
     * defaults to static::RESOURCE
     * 
     * @var string
     */
    private $currentResource;

    /**
     * {@inheritDoc}
     */
    public function has($value, string $column = 'id'): bool
    {
        if (is_numeric($value)) {
            $value = (float) $value;
        }

        $model = static::MODEL;

        return $model::where($column, $value)->exists();
    }

    /**
     * Use the given resource class
     * 
     * @param  string $resourceClass
     * @return $this
     */
    public function useResource(string $resourceClass): self
    {
        $this->currentResource = $resourceClass;

        return $this;
    }

    /**
     * Get current used resource class name
     * 
     * @return string
     */
    public function getResourceClass(): string
    {
        if ($this->currentResource) return $this->currentResource;

        if (!empty(static::APPS_RESOURCES)) {
            $appType = config('app.type');

            if (!empty(static::APPS_RESOURCES[$appType])) return static::APPS_RESOURCES[$appType];
        }

        return $this->currentResource ?: static::RESOURCE;
    }

    /**
     * Get a normal record by id
     * Please use the `get` method to get full details about the record
     *
     * @param  int $id
     * @param  array $otherOptions
     * @return mixed
     */
    public function first(int $id, array $otherOptions = [])
    {
        $otherOptions['id'] = $id;

        return $this->list($otherOptions)->first();
    }

    /**
     * Get list of models for the given options
     *
     * @param  array $options
     * @return Illuminate\Support\Collection
     */
    public function listModels(array $options)
    {
        $options['as-model'] = true;

        return $this->list($options);
    }

    /**
     * Get total records based on given options
     *
     * @param array $options
     * @return int
     */
    public function total(array $options)
    {
        $options['paginate'] = false;
        unset($options['page']);

        $this->initiateListing($options);

        return $this->query->count();
    }

    /**
     * Initiate listing info
     *
     * @param  array $options
     * @return void
     */
    protected function initiateListing(array $options)
    {
        $this->setOptions($options);

        $this->query = $this->getQuery();

        $this->select();

        $filterManger = new FilterManager($this->query, $options, static::FILTER_BY);
        $filterManger->filter(array_merge(static::FILTERS, config('mongez.filters', [])));

        $this->filter();

        $defaultOrderBy = [];

        if ($orderBy = $this->option('orderBy')) {
            $defaultOrderBy = $orderBy;
        } elseif (!empty(static::ORDER_BY)) {
            $defaultOrderBy = [$this->column(static::ORDER_BY[0]), static::ORDER_BY[1]];
        }

        $this->orderBy($defaultOrderBy);
    }

    /**
     * Get publish Model
     *
     * @param int $id
     * @return Model|null
     */
    public function getPublishedModel($id)
    {
        $model = $this->getModel($id);

        if (!$model->{$this->getPublishedColumn()}) return null;

        return $model;
    }

    /**
     * Get publish item
     *
     * @param int $id
     * @return Resource|null
     */
    public function getPublished($id)
    {
        $item = $this->get((int) $id);

        if (!$item || !$item->{$this->getPublishedColumn()}) return null;

        return $item;
    }

    /**
     * Get published items
     *
     * @param array $options
     * @return Collection
     */
    public function listPublished(array $options = [])
    {
        $options[$this->getPublishedColumn()] = true;

        return $this->list($options);
    }

    /**
     * Alias to listPublished
     *
     * @deprecated
     * @param array $options
     * @return Collection
     */
    public function published(array $options = [])
    {
        return $this->listPublished($options);
    }

    /**
     * Publish/Unpublish the model id
     *
     * @param int $id
     * @param bool $publishState
     * @return void
     */
    public function publish($id, $publishState)
    {
        $this->getQuery()->where('id', (int) $id)->update([
            $this->getPublishedColumn() => (bool) $publishState
        ]);
    }

    /**
     * Set pagination info from pagination data
     *
     * @param object $data
     * @return void
     */
    protected function setPaginateInfo($data)
    {
        $this->paginationInfo = [
            'currentResults' => $data->count(),
            'totalRecords' => $data->total(),
            'numberOfPages' => $data->lastPage(),
            'itemsPerPage' => $data->perPage(),
            'currentPage' => $data->currentPage()
        ];
    }

    /**
     * Get pagination info
     *
     * @return array $paginationInfo
     */
    public function getPaginateInfo(): array
    {
        return $this->paginationInfo;
    }

    /**
     * Wrap the given model to its resource
     *
     * @param \Model $model
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function wrap($model): JsonResource
    {
        if (is_array($model)) {
            $modelName = static::MODEL;
            $model = new $modelName($model);
        }

        $resource = $this->getResourceClass();
        return new $resource($model);
    }

    /**
     * Wrap the given collection into collection of resources
     *
     * @param \Illuminate\Support\Collection $collection
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function wrapMany($collection)
    {
        $collection = collect($collection)->map(function ($item) {
            if (is_array($item)) {
                $modelName = static::MODEL;
                $item = new $modelName($item);
            }

            return $item;
        });

        $resource = $this->getResourceClass();
        return $resource::collection($collection);
    }

    /**
     * This method mainly used to filtering records `the where clause`
     *
     * @return void
     */
    abstract protected function filter();

    /**
     * Manage Selected Columns
     *
     * @return void
     */
    abstract protected function select();

    /**
     * Perform records ordering
     *
     * @param   array $orderBy
     * @return  void
     */
    protected function orderBy(array $orderBy)
    {
        if (empty($orderBy)) return;

        // If there is no zero index in the array
        // it means the order will be for multiple columns
        if (!isset($orderBy[0])) {
            foreach ($orderBy as $column => $columnOrder) {
                $this->query->orderBy($column, $columnOrder);
            }
        } else {
            $this->query->orderBy(...$orderBy);
        }
    }

    /**
     * Adjust records that were fetched from database
     *
     * @param \Illuminate\Support\Collection $records
     * @return \Illuminate\Support\Collection
     */
    protected function records(Collection $records): Collection
    {
        return $records->map(function ($record) {
            if (!empty(static::ARRAYBLE_DATA)) {
                foreach (static::ARRAYBLE_DATA as $column) {
                    $record[$column] = json_encode($record[$column]);
                }
            }

            return $record;
        });
    }

    /**
     * Set options list
     *
     * @param array $options
     * @return void
     */
    protected function setOptions(array $options): void
    {
        $this->options = $options;

        $selectColumns = (array) $this->option('select');

        $this->select = new Select($selectColumns);
    }

    /**
     * Get option value
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    protected function option(string $key, $default = null)
    {
        $value = Arr::get($this->options, $key, $default);

        if ($value === 'false') {
            $value = false;
        } elseif ($value === 'true') {
            $value = true;
        }

        return $value;
    }

    /**
     * Get published column
     * 
     * @return string
     */
    protected function getPublishedColumn(): string
    {
        return defined('static::PUBLISHED_COLUMN') ? static::PUBLISHED_COLUMN :
            config('mongez.repository.publishedColumn', static::DEFAULT_PUBLISHED_COLUMN);
    }

    /**
     * A shorthand method for filtering data if they are available
     * 
     * @param  string $column
     * @param  string|null $option
     * @return $this
     */
    protected function where(string $column, string $option = null): self
    {
        if (!$option) {
            $option = $column;
        }

        if ($optionValue = $this->option($option)) {
            $this->query->where($column, $optionValue);
        }

        return $this;
    }

    /**
     * A shorthand method for filtering data if they are available
     * 
     * @param  string $column
     * @param  string|null $option
     * @return $this
     */
    protected function whereIn(string $column, string $option = null): self
    {
        if (!$option) {
            $option = $column;
        }

        if ($optionValue = $this->option($option)) {
            $this->query->whereIn($column, (array) $optionValue);
        }

        return $this;
    }

    /**
     * A shorthand method for filtering data if they are available
     * 
     * @param  string $column
     * @param  string|null $option
     * @return $this
     */
    protected function whereInInt(string $column, string $option = null): self
    {
        if (!$option) {
            $option = $column;
        }

        if ($optionValue = $this->option($option)) {
            $this->query->whereInInt($column, array_map('intval', (array) $optionValue));
        }

        return $this;
    }
}
