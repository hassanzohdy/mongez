<?php
namespace App\Modules\Users\Resources;

use ResourceManager;

class User extends ResourceManager
{
    /**
     * {@inheritDoc}
     */
    const DATA = ['id', 'name', 'email'];
}