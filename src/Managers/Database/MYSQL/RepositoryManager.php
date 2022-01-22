<?php

namespace HZ\Illuminate\Mongez\Managers\Database\MYSQL;

use HZ\Illuminate\Mongez\Contracts\Repositories\RepositoryInterface;
use HZ\Illuminate\Mongez\Managers\Database\RepositoryManager as BaseRepositoryManager;

abstract class RepositoryManager extends BaseRepositoryManager implements RepositoryInterface
{
    /**
     * Set if the current repository uses a soft delete method or not
     * This is mainly used in the where clause
     *
     * @var bool
     */
    const USING_SOFT_DELETE = true;

    /**
     * Deleted at column
     *
     * @const string
     */
    const DELETED_AT = 'deleted_at';

    /**
     * Table alias
     *
     * @const string
     */
    const TABLE_ALIAS = '';

    /**
     * Retrieve only the active `un-deleted` records
     *
     * @const string
     */
    const RETRIEVE_ACTIVE_RECORDS = 'ACTIVE';

    /**
     * Retrieve All records
     *
     * @const string
     */
    const RETRIEVE_ALL_RECORDS = 'ALL';

    /**
     * Retrieve Deleted records
     *
     * @const string
     */
    const RETRIEVE_DELETED_RECORDS = 'DELETED';

    /**
     * Retrieval mode keyword to be used in the options list flag
     *
     * @const string
     */
    const RETRIEVAL_MODE = 'retrievalMode';

    /**
     * Default retrieval mode
     *
     * @const string
     */
    const DEFAULT_RETRIEVAL_MODE = self::RETRIEVE_ACTIVE_RECORDS;

    /**
     * {@inheritdoc}
     */
    protected function initiateListing(array $options)
    {
        parent::initiateListing($options);

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
    }
    /**
     * Get table name of the primary model of the repo
     *
     * @return string
     */
    public function getTableName(): string
    {
        return static::TABLE ?: (static::MODEL)::getTableName();
    }

    /**
     * Get the table name that will be used in the query
     *
     * @return string
     */
    protected function tableName(): string
    {
        return static::TABLE_ALIAS ? static::TABLE . ' as ' . static::TABLE_ALIAS : static::TABLE;
    }

    /**
     * Get the table name that will be used in the rest of the query like select, where...etc
     *
     * @return string
     */
    protected function columnTableName(): string
    {
        return static::TABLE_ALIAS ?: static::TABLE;
    }

    /**
     * Pare the given arrayed value
     *
     * @param array $value
     * @return mixed
     */
    protected function handleArrayableValue(array $value)
    {
        return json_encode($value);
    }

    /**
     * Set localized data automatically from the LOCALIZED_DATA array
     *
     * @param  \Model $model
     * @param  \Request $request
     * @return void
     */
    protected function setLocalizedData($model)
    {
        foreach (static::LOCALIZED_DATA as $column) {
            if ($this->isIgnorable($column)) continue;

            // adjust it in sql
            $this->setToModel($model, $column, $this->input($column));
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
        return $this->table . '.' . $column;
    }
}
