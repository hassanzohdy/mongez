<?php

namespace App\Modules\Users\Models;

use HZ\Illuminate\Mongez\Database\Eloquent\MYSQL\Model;

class UserGroup extends Model
{

    /**
     * {@inheritDoc}
     */
    protected $table = 'users_groups';
}
