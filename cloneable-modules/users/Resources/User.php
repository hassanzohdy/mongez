<?php
namespace App\Modules\Users\Resources;

use HZ\Illuminate\Mongez\Managers\Resources\JsonResourceManager;

class User extends JsonResourceManager
{
    /**
     * {@inheritDoc}
     */
    const DATA = ['id', 'name', 'email','user_group_id'];

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
