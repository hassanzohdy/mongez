<?php

namespace App\Modules\Users\Models;


use Illuminate\Contracts\Auth\Authenticatable;
use App\Modules\Users\Traits\Auth\updatePassword;
use HZ\Illuminate\Mongez\Traits\MongoDB\RecycleBin;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use HZ\Illuminate\Mongez\Database\Eloquent\MongoDB\Model;

class User extends Model implements Authenticatable
{
    use AuthenticatableTrait, updatePassword, RecycleBin;

    /**
     * {@inheritDoc}
     */
    protected $collection = 'users';

    /**
     * Get shared info for the user that will be stored as a sub document of another collection
     * 
     * @return array
     */
    public function sharedInfo(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
