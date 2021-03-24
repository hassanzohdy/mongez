<?php

namespace HZ\Illuminate\Mongez\Traits\Repository;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use HZ\Illuminate\Mongez\Helpers\Repository\Select;
use HZ\Illuminate\Mongez\Helpers\Filters\FilterManager;

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
        $$options['as-model'] = true;

        return $this->list($otherOptions);
    }

    /**
     * Get total records based on given options
     *
     * @param array $options
     * @return int
     */
    public function total(array $options)
    {
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

        $this->table = $this->columnTableName();

        $this->select();

        if (static::USING_SOFT_DELETE === true) {
            $retrieveMode = $this->option(static::RETRIEVAL_MODE, static::DEFAULT_RETRIEVAL_MODE);

            if ($retrieveMode == static::RETRIEVE_ACTIVE_RECORDS) {
                $deletedAtColumn = $this->column(static::DELETED_AT);

                $this->query->whereNull($deletedAtColumn);
            } elseif ($retrieveMode == static::RETRIEVE_DELETED_RECORDS) {
                $deletedAtColumn = $this->column(static::DELETED_AT);
                $this->query->whereNotNull($deletedAtColumn);
            }
        }

        $filterManger = new FilterManager($this->query, $options, static::FILTER_BY);
        $filterManger->merge(array_merge(static::FILTERS, config('mongez.filters', [])));

        $this->filter();

        $defaultOrderBy = [];

        if ($orderBy = $this->option('orderBy')) {
            $defaultOrderBy = $orderBy;
        } elseif (!empty(static::ORDER_BY)) {
            $defaultOrderBy = [$this->column(static::ORDER_BY[0]), static::ORDER_BY[1]];
        }

        $this->orderBy($this->option('orderBy', $defaultOrderBy));
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

        if (!$model->published) return null;

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
        $item = $this->get($id);

        if (!$item || !$item->published) return null;

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
        $options['published'] = true;

        return $this->list($options);
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
            'published' => (bool) $publishState
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
     * @return \JsonResource
     */
    public function wrap($model): JsonResource
    {
        if (is_array($model)) {
            $modelName = static::MODEL;
            $model = new $modelName($model);
        }

        $resource = static::RESOURCE;
        return new $resource($model);
    }

    /**
     * Wrap the given collection into collection of resources
     *
     * @param \Illuminate\Support\Collection $collection
     * @return \JsonResource
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

        $resource = static::RESOURCE;
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
        return Arr::get($this->options, $key, $default);
    }
}
