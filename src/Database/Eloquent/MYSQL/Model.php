<?php

namespace HZ\Illuminate\Mongez\Database\Eloquent\MYSQL;

use Illuminate\Database\Eloquent\Model as BaseModel;
use HZ\Illuminate\Mongez\Database\Eloquent\ModelTrait;

class Model extends BaseModel
{
    use ModelTrait;

    /**
     * Created By column
     * Set it to false if this column doesn't exist in the table
     *
     * @const string|bool
     */
    const CREATED_BY = 'created_by';

    /**
     * Updated By column
     * Set it to false if this column doesn't exist in the table
     *
     * @const string|bool
     */
    const UPDATED_BY = 'updated_by';

    /**
     * Deleted By column
     * Set it to false if this column doesn't exist in the table
     *
     * @const string|bool
     */
    const DELETED_BY = 'deleted_by';

    /**
     * Disable guarded fields
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get user id that will be used with created by, updated by and deleted by
     * 
     * @return int
     */
    protected function byUser()
    {
        return user()->id ?? 0;
    }
}
