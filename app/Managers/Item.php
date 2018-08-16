<?php
namespace App\Managers;

use Illuminate\Support\Fluent;

abstract class Item extends Fluent
{
    /**
     * Determine whether the item is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->attributes);
    }
}