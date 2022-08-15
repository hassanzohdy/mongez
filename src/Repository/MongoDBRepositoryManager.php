<?php

namespace HZ\Illuminate\Mongez\Repository;

use Illuminate\Support\Collection;
use HZ\Illuminate\Mongez\Database\Eloquent\MongoDB\Model;
use HZ\Illuminate\Mongez\Database\Eloquent\MongoDB\Aggregate\Aggregate;

abstract class MongoDBRepositoryManager extends RepositoryManager implements RepositoryInterface
{
    /**
     * If set to true, the multiple uploads column paths will be json encoded while storing it in database.
     *
     * @const bool
     */
    const SERIALIZE_MULTIPLE_UPLOADS = false;

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
     * Geo Location data 
     * 
     * @const array
     */
    const LOCATION_DATA = [];

    /**
     * Get the table name that will be used in the query 
     * 
     * @return string
     */
    protected function tableName(): string
    {
        return $this->getTableName();
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
            if ($this->option('as-model', false) === true) return $record;

            $resource = static::RESOURCE;
            return new $resource((object) $record);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function get(int $id)
    {
        return $this->getBy('id', (int) $id);
    }

    /**
     * Pare the given arrayed value
     *
     * @param array $value
     * @return mixed
     */
    protected function handleArrayableValue(array $value)
    {
        return $value;
    }

    /**
     * Get Aggregation framework
     * 
     * @param  ?QueryBuilder $query
     * @return Aggregate
     */
    public function aggregate($query = null)
    {
        return new Aggregate($query ?: $this->getQuery());
    }

    /**
     * Get model for the given id
     * 
     * @param  int|array|Model $id
     * @return mixed
     */
    public function getModel($id)
    {
        if ($id instanceof Model) {
            return $id;
        }

        return $this->getByModel('id', (int) $id);
    }

    /**
     * Get shared info data for the given options
     * 
     * @param array $options
     * @param string $sharedInfoMethod
     * @return array
     */
    public function listSharedInfo(array $options, string $sharedInfoMethod = 'sharedInfo')
    {
        return $this->listModels($options)->map(function ($model) use ($sharedInfoMethod) {
            return $model->$sharedInfoMethod();
        })->toArray();
    }

    /**
     * Get shared info for the given id
     * 
     * @param  int $id
     * @param  string $sharedInfoMethod
     * @return mixed
     */
    public function sharedInfo($id, string $sharedInfoMethod = 'sharedInfo')
    {
        $model = $this->getModel($id);

        return $model ? $model->$sharedInfoMethod() : null;
    }

    /**
     * Get by the given column name
     * 
     * @param  string $column
     * @param  mixed value
     * @return mixed
     */
    public function getBy($column, $value)
    {
        if ($this->isCachable()) {
            $cacheKey = static::NAME . '_' . $column . '_' . (string) $value;
            $record = $this->getCache($cacheKey);

            if (!$record) {
                $record = $this->getByModel($column, $value);

                if (!$record) return null;

                $this->setCache($$cacheKey, $record->toArray());
            } else {
                $record = $this->newModel($record);
            }

            return $this->wrap($record);
        }

        $record = $this->getByModel($column, $value);

        return $record ? $this->wrap($record) : null;
    }

    /**
     * Get the current model by the given column name and value
     * 
     * @param  string $column
     * @param  mixed value
     * @return mixed
     */
    public function getByModel($column, $value)
    {
        $model = static::MODEL;

        return is_array($value) ? $model::whereIn($column, $value)->get() : $model::where($column, $value)->first();
    }

    /**
     * {@inheritDoc}
     */
    protected function setData($model, $request)
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function select()
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function filter()
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function setAutoData($model)
    {
        parent::setAutoData($model);
        // add the extra methods
        $this->setDocumentData($model);
        $this->setMultiDocumentData($model);
        $this->setLocationData($model);
    }

    /**
     * Set location data
     * 
     * @param  Model $model
     * @return void
     */
    protected function setLocationData($model)
    {
        foreach (static::LOCATION_DATA as $locationKey) {
            $location = $this->input($locationKey);

            if ($location) {
                $model->$locationKey = [
                    'type' => 'Point',
                    'coordinates' => [(float) $location['lat'] ?? 0, (float) $location['lng'] ?? 0],
                    'address' => $location['address'] ?? null,
                ];
            }
        }
    }

    /**
     * Set document data to column
     *
     * @param  \Model $model
     * @return void     
     */
    protected function setDocumentData($model)
    {
        foreach (static::DOCUMENT_DATA as $column => $documentModelClass) {
            if ($this->isIgnorable($column)) continue;

            if (is_array($documentModelClass)) {
                list($class, $sharedInfoMethod) = $documentModelClass;
                $documentModelClass = $class;
            } else {
                $sharedInfoMethod = 'sharedInfo';
            }

            $value = $this->input($column);

            $documentModel = $value instanceof Model ? $value : $documentModelClass::find((int) $value);

            $model->$column = $documentModel ? $documentModel->{$sharedInfoMethod}() : null;
        }
    }

    /**
     * Filter by geo locations.
     *
     * @param string $column
     * @param float $distance
     * @return void
     */
    public function whereNearBy($column, $distance)
    {
        $location = $this->option($column);

        if (!$location) return;

        $this->query->whereLocationNear($column, [(float) $location['lat'], (float) $location['lng']], $distance);
    }

    /**
     * A shorthand method for filtering data if they are available
     * 
     * @param  string $column
     * @param  string|null $option
     * @return $this
     */
    protected function whereBool(string $column, string $option = null): self
    {
        if (!$option) {
            $option = $column;
        }

        if (($optionValue = $this->option($option)) !== null) {
            $this->query->where($column, (bool) $optionValue);
        }

        return $this;
    }

    /**
     * Set Multi documents data to column value.
     *
     * @param  \Model $model
     * @return void     
     */
    protected function setMultiDocumentData($model)
    {
        foreach (static::MULTI_DOCUMENTS_DATA as $column => $documentModelClass) {
            if ($this->isIgnorable($column)) continue;

            $value = $this->input($column);

            if (!$value) {
                $model->$column = [];
                continue;
            }

            $ids = array_map('intVal', $value);

            if (is_array($documentModelClass)) {
                list($class, $method) = $documentModelClass;
                $documentModelClass = $class;
            } else {
                $method = 'sharedInfo';
            }

            $records = $documentModelClass::whereIn('id', $ids)->get();

            $records = $records->map(function ($record) use ($method) {
                return $record->$method();
            })->toArray();

            // make sure it is stored in same order as sent from request
            if (count($ids) > 1) {
                $recordsValues = array_flip($ids);
                usort($records, function ($recordA, $recordB) use ($recordsValues) {
                    if ($recordsValues[$recordA['id']] === $recordsValues[$recordB['id']]) return 0;
                    if ($recordsValues[$recordA['id']] < $recordsValues[$recordB['id']]) return -1;

                    return 1;
                });
            }

            $model->$column = $records;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function disassociate($id, $model, $key)
    {
        $model = $this->getModel($id);

        if (!$model) return;

        $model->disassociate($model, $key)->save();
    }

    /**
     * {@inheritDoc}
     */
    public function reassociate($id, $model, $key)
    {
        $model = $this->getModel($id);

        if (!$model) return;

        $model->reassociate($model, $key)->save();
    }

    /**
     * {@inheritDoc}
     */
    protected function boot()
    {
    }

    /**
     * Get column name appended by table|table alias
     *
     * @param  string $column
     * @return string
     */
    protected function column(string $column): string
    {
        return $column;
    }
}
