<?php
namespace App\Http\Resources\ResourcePath;

use App\Managers\Resources\MongoJsonResource;

class ResourceName extends MongoJsonResource 
{
    /**
     * {@inheritDoc}
     */
    const DATA = [DATA_LIST];

    /**
     * {@inheritDoc}
     */
    const LOCALIZED = [];

    /**
     * {@inheritDoc}
     */
    const WHEN_AVAILABLE = [];

    /**
     * {@inheritDoc}
     */
    const COLLECTABLE = [];
}