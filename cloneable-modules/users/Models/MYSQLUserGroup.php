<?php
namespace App\Modules\Users\Models;

use HZ\Illuminate\Mongez\Managers\Database\mysql\Model;

class UserGroup extends Model 
{
    
    /**
     * {@inheritDoc}
     */
    protected $table = 'users_groups';
}