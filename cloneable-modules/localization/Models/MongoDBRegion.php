<?php

namespace App\Modules\Localization\Models;

use HZ\Illuminate\Mongez\Database\Eloquent\MongoDB\Model;

class Region extends Model
{
    /**
     * {@inheritDoc}
     */
    public function sharedInfo(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'city'        => $this->city,
        ];
    }
}
