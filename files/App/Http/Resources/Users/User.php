<?php
namespace App\Http\Resources\Users;

use ResourceManager;

class User extends ResourceManager
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
        if (! empty($this->accessTokens)) {
            $this->data['accessToken'] = $this->accessTokens[0]['token'];
        }
    }
}