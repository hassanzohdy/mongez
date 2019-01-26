<?php
namespace App\Http\Resources\Support;

use App\Managers\Resources\MongoJsonResource;

class Contact extends MongoJsonResource 
{
    /**
     * {@inheritDoc}
     */
    const DATA = ['id', 'email', 'subject', 'message', 'reply'];

    /**
     * {@inheritDoc}
     */
    const DATES = ['createdAt', 'updatedAt'];
}