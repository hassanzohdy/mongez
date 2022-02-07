<?php

namespace App\Modules\Users\Models;

use HZ\Illuminate\Mongez\Database\Eloquent\MongoDB\Model;

class DeviceToken extends Model
{
    /**
     * {@Inheritdoc}
     */
    const SHARED_INFO = ['id', 'type', 'token'];
}
