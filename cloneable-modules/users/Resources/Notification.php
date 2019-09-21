<?php
namespace App\Modules\Users\Resources;

use HZ\Illuminate\Mongez\Managers\Resources\JsonResourceManager;

class Notification extends JsonResourceManager
{
    /**
     * {@inheritDoc}
     */
    const DATA = ['id', 'type', 'extra', 'seen'];

    /**
     * {@inheritDoc}
     */
    const DATES = ['createdAt'];

    /**
     * {@inheritDoc}
     */
    const ASSETS = ['image'];
}