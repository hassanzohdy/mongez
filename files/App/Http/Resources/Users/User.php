<?php
namespace App\Http\Resources\Users;

use App\Managers\Resources\MongoJsonResource;

class User extends MongoJsonResource
{
    /**
     * {@inheritDoc}
     */
    const DATA = ['id', 'name', 'email'];

    /**
     * {@inheritDoc}
     */
    protected function extend($request) 
    {
        if ($this->accessTokens) {
            $this->data['accessToken'] = $this->accessTokens[0]['token'];
        }
    }
}