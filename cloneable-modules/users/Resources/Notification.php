<?php
namespace App\Modules\Users\Resources;

use HZ\Illuminate\Organizer\Managers\Resources\JsonResourceManager;

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