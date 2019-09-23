<?php
namespace App\Modules\Users\Models;


use Illuminate\Contracts\Auth\Authenticatable;
use App\Modules\Users\Traits\Auth\updatePassword;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use HZ\Illuminate\Mongez\Managers\Database\MYSQL\Model;

class User extends Model implements Authenticatable
{
    use AuthenticatableTrait, updatePassword;

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
