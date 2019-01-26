<?php
namespace App\Http\Resources\Marketing;

use App\Managers\Resources\MongoJsonResource;

class Subscriber extends MongoJsonResource 
{
    /**
     * {@inheritDoc}
     */
    const DATA = ['id', 'email'];
}