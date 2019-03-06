<?php
namespace App\Http\Resources\Users;

use ResourceManager;

class User extends ResourceManager
{
    /**
     * {@inheritDoc}
     */
    const DATA = ['id', 'name', 'email'];
}