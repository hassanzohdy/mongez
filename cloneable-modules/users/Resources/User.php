<?php
namespace App\Modules\Users\Resources;

use App\Modules\Tasks\Resources\Task;
use App\Modules\Departments\Resources\Department;
use HZ\Illuminate\Mongez\Managers\Resources\JsonResourceManager;

class User extends JsonResourceManager
{
    /**
     * {@inheritDoc}
     */
    const DATA = ['id', 'name', 'email'];

    /**
     * {@inheritDoc}
     */
    const ASSETS = [];

    /**
     * {@inheritDoc}
     */
    const WHEN_AVAILABLE = [];

    /**
     * {@inheritDoc}
     */
    const RESOURCES = [
    ];

    /**
     * {@inheritDoc}
     */
    const COLLECTABLE = [];
}
