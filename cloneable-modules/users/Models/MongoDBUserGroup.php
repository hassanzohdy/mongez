<?php
namespace App\Modules\Users\Models;

use HZ\Illuminate\Mongez\Managers\Database\MongoDB\Model;

class UserGroup extends Model 
{
    
    /**
     * {@inheritDoc}
     */
    protected $collection = 'users_groups';
}