<?php

namespace App\Modules\Users\Models;

use HZ\Illuminate\Mongez\Database\Eloquent\MongoDB\Model;

class Permission extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $collection = 'permissions';
}
