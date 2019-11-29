<?php
namespace App\Modules\Users\Models;

use HZ\Illuminate\Mongez\Managers\Database\MongoDB\Model;

class Permission extends Model 
{
    /**
     * {@inheritDoc}
     */
    protected $collection = 'permissions';
}