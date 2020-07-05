<?php
namespace App\Modules\Localization\Models;

use HZ\Illuminate\Mongez\Managers\Database\MongoDB\Model;

class City extends Model 
{
    /**
     * {@inheritDoc}
     */
    public function sharedInfo(): array
    {
        return [
            'id'      => $this->id,
            'name' => $this->name,
            'country' => $this->country
        ];
    }
}