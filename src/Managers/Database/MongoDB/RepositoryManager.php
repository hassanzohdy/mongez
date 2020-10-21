<?php

namespace HZ\Illuminate\Mongez\Managers\Database\MongoDB;

use Illuminate\Support\Collection;
use HZ\Illuminate\Mongez\Helpers\Database\MongoDB\Aggregation;
use HZ\Illuminate\Mongez\Helpers\Filters\MongoDB\Filter;
use HZ\Illuminate\Mongez\Contracts\Repositories\RepositoryInterface;
use HZ\Illuminate\Mongez\Managers\Database\MYSQL\RepositoryManager as BaseRepositoryManager;
use Illuminate\Support\Facades\App;

abstract class RepositoryManager extends BaseRepositoryManager implements RepositoryInterface
{
    /**
     * Filter class.
     *  
     * @const string
     */
    const FILTER_CLASS = Filter::class;

    /**
     * Set if the current repository uses a soft delete method or not
     * This is mainly used in the where clause
     * 
     * @var bool
     */
    const USING_SOFT_DELETE = false;

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
        if (static::USING_CACHE) return $this->wrap($this->getCache((int) $id));
        
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
     * @param  int|array $id
     * @return mixed
     */
    public function getModel($id)
    {
        if (is_array($id)) {
            $id = array_map('intval', $id);
        } else {
            $id = (int) $id;
        }

        return $this->getByModel('id', $id);
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
     * Get the query handler
     * 
     * @return mixed
     */
    public function getQuery()
    {
        $model = static::MODEL;
        return $model::where('id', '!=', -1);
    }

    /**
     * Get the table name that will be used in the rest of the query like select, where...etc
     * 
     * @return string
     */
    protected function columnTableName(): string
    {
        return static::TABLE;
    }

    /**
     * {@inheritDoc}
     */
    protected function setAutoData($model, $request)
    {
        parent::setAutoData($model, $request);
        // add the extra methods
        $this->setDocumentData($model, $request);
        $this->setMultiDocumentData($model, $request);
    }

    /**
     * {@inheritDoc} 
     */
    protected function column(string $column): string
    {
        return $column;
    }

    /**
     * Set document data to column
     *
     * @param  \Model $model
     * @param  \Request $request
     * @return void     
     */
    protected function setDocumentData($model, $request)
    {
        foreach (static::DOCUMENT_DATA as $column => $documentModelClass) {
            if (is_array($documentModelClass)) {
                list($class, $method) = $documentModelClass;
                $documentModelClass = $class;
            } else {
                $method = 'sharedInfo';
            }

            $documentModel = $documentModelClass::find((int) $request->$column);

            $model->$column = $documentModel ? $documentModel->{$method}() : [];
        }
    }

    /**
     * Set Multi documents data to column value.
     *
     * @param  \Model $model
     * @param  \Request $request
     * @return void     
     */
    protected function setMultiDocumentData($model, $request)
    {
        foreach (static::MULTI_DOCUMENTS_DATA as $column => $documentModelClass) {
            if (!$request->$column) {
                $model->$column = [];
                continue;
            }

            $ids = array_map('intVal', $request->$column);
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

                    list($parentRepositoryClass, $childNameInParent) = $parentRepositoryWithChildColumnName;

                    $parentRepository = App::make($parentRepositoryClass);

                    $parentRepository->disassociate($parentId, $model, $childNameInParent);
                }
            });
        }
    }

    /**
     * @inheritDoc
     */
    protected function filterBy($filter)
    {
        return $filter->merge(
            self::FILTER_CLASS
        );
    }
}
