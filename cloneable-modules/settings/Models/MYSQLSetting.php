<?php
namespace App\Modules\Settings\Models;

use HZ\Illuminate\Mongez\Managers\Database\mysql\Model;

class Setting extends Model 
{
    /**
     * Get the value of setting if file.
     *
     * @param  string  $value
     * @return string
     */
    public function getValueAttribute($value)
    {
        if ($this->type == 'file') {
            return url($value);
        }
        return $value;
    }
}