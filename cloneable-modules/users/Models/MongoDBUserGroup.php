<?php

namespace App\Modules\Users\Models;

use HZ\Illuminate\Mongez\Database\Eloquent\MongoDB\Model;

class UserGroup extends Model
{

    /**
     * {@inheritDoc}
     */
    protected $collection = 'usersGroups';
}
