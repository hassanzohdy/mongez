<?php

namespace HZ\Illuminate\Mongez\Managers\Database\MongoDB;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use HZ\Illuminate\Mongez\Helpers\Filters\MongoDB\Filter;
use HZ\Illuminate\Mongez\Helpers\Database\MongoDB\Aggregation;
use HZ\Illuminate\Mongez\Contracts\Repositories\RepositoryInterface;
use HZ\Illuminate\Mongez\Managers\Database\RepositoryManager as BaseRepositoryManager;

abstract class RepositoryManager extends BaseRepositoryManager implements RepositoryInterface
{
    /**
     * Filter class.
     *  
     * @const string
     */
    const FILTER_CLASS = Filter::class;

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
     * Set of the parents repositories of current repo
     * 
     * @const array
     */
    const CHILD_OF = [];

    /**
     * Set of the children repositories of current repo
     * 
     * @const array
     */
    const PARENT_OF = [];

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
        if (static::USING_CACHE) return $this->wrap($this->getCache($id));

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
     * @return Aggregation
     */
    public function aggregate()
    {
        return new Aggregation($this->getQuery());
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

        if (is_array($id)) {
            $id = array_map('intval', $id);
        } else {
            $id = (int) $id;
        }

        return $this->getByModel('id', $id);
    }

    /**
     * Get shared info data for the given options
     * 
     * @param array $options
     * @param string $sharedInfoMethod
     * @return Collection
     */
    public function ListSharedInfo(array $options, string $sharedInfoMethod = 'sharedInfo')
    {
        $options['as-model'] = true;

        return $this->list($options)->map(function ($model) use ($sharedInfoMethod) {
            return $model->$sharedInfoMethod();
        })->toArray();
    }

    /**
     * Get shared info for the given id
     * 
     * @param int $id
     * @return mixed
     */
    public function sharedInfo($id)
    {
        $model = $this->getModel($id);

        return $model ? $model->sharedInfo() : null;
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
        $resource = static::RESOURCE;

        $object = $this->getByModel($column, $value);

        return $object ? new $resource($object) : null;
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
                    'coordinates' => [(float) $location['lat'], (float) $location['lng']],
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
                list($class, $method) = $documentModelClass;
                $documentModelClass = $class;
            } else {
                $method = 'sharedInfo';
            }

            $documentModel = $documentModelClass::find((int) $this->input($column));

            $model->$column = $documentModel ? $documentModel->{$method}() : null;
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
            $records = $documentModelClass::whereIn('id', $ids)->get();

            $records = $records->map(function ($record) {
                return $record->sharedInfo();
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
        if (!empty(static::PARENT_OF)) {
            $this->events->subscribe($this->eventName . '.delete', function ($model, $id) {
                foreach (static::PARENT_OF as $childColumnName => $childRepository) {
                    $childrenList = $model->$childColumnName ?? [];

                    $childRepository = App::make($childRepository);

                    foreach ($childrenList as $child) {
                        $childRepository->delete($child['id']);
                    }
                }
            });
        }

        if (!empty(static::CHILD_OF)) {
            $this->events->subscribe($this->eventName . '.delete', function ($model, $id) {
                foreach (static::CHILD_OF as $parentColumnName => $parentRepositoryWithChildColumnName) {
                    $parentId = $model->$parentColumnName['id'] ?? null;
                    if (!$parentId) continue;

                    [$parentRepositoryClass, $childNameInParent] = $parentRepositoryWithChildColumnName;

                    // as the third value in the array is optional, we'll separate it from the list function
                    $sharedInfoMethod = $parentRepositoryWithChildColumnName[2] ?? 'sharedInfo';

                    $parentRepository = App::make($parentRepositoryClass);

                    $this->updateParentForChild($parentId, $parentRepository, $model, $sharedInfoMethod, $childNameInParent, 'disassociate');
                }
            });

            $this->events->subscribe($this->eventName . '.save', function ($model, $request, $oldModel = null) {
                foreach (static::CHILD_OF as $parentColumnName => $parentRepositoryWithChildColumnName) {
                    $parentId = $model->$parentColumnName['id'] ?? null;

                    if (!$parentId) continue;

                    [$parentRepositoryClass, $childNameInParent] = $parentRepositoryWithChildColumnName;

                    $sharedInfoMethod = $parentRepositoryWithChildColumnName[2] ?? 'sharedInfo';

                    $parentRepository = App::make($parentRepositoryClass);

                    $this->updateParentForChild($parentId, $parentRepository, $model, $sharedInfoMethod, $childNameInParent, 'reassociate');

                    if ($oldModel && ($oldModel->$parentColumnName['id'] ?? null) != $parentId) {
                        $this->updateParentForChild($oldModel->$parentColumnName['id'], $parentRepository, $oldModel, $sharedInfoMethod, $childNameInParent, 'disassociate');
                    }
                }
            });
        }
    }

    /**
     * Update Parent and trigger save event
     * 
     * @param  int $parentId
     * @param  RepositoryManager $parentRepository
     * @param  Model $childModel
     * @param  string $sharedInfoMethod
     * @param  string $childNameInParent
     * @param  string $associateMode reassociate|disassociate
     * @return void
     */
    protected function updateParentForChild($parentId, $parentRepository, $childModel, $sharedInfoMethod, $childNameInParent, $associateMode)
    {
        $parentModel = $parentRepository->getModel($parentId);

        if ($parentModel) {
            $parentModel->$associateMode($childModel->$sharedInfoMethod(), $childNameInParent)->save();

            $parentRepository->trigger('save', $parentModel, $this->request, $parentModel);
        }
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
