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

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->send();
    }

    /**
     * An alias to `jsonSerialize Method` that will be used to send the item data on json serializing
     * 
     * @return array
     */
    public function send(): array 
    {
        return $this->toArray();
    }
}