<?php
namespace App\Modules\Localization\Models;

use HZ\Illuminate\Mongez\Managers\Database\MongoDB\Model;

class Currency extends Model 
{
    /**
     * {@inheritDoc}
     */
    public function sharedInfo(): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'symbol'  => $this->symbol,
            'code'    => $this->code,
            'status'  => $this->status
        ];
    }
}