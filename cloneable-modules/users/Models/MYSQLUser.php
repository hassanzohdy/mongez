<?php
namespace App\Modules\Users\Models;


use Illuminate\Contracts\Auth\Authenticatable;
use App\Modules\Users\Traits\Auth\updatePassword;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use HZ\Illuminate\Mongez\Managers\Database\MYSQL\Model;

class User extends Model implements Authenticatable
{
    use AuthenticatableTrait, updatePassword;
}
