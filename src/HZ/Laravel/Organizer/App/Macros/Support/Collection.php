<?php
namespace HZ\Laravel\Organizer\App\Macros\Support;

use Illuminate\Support\Arr;

class Collection
{
    /**
     * Execute the given callback on the collection items without returning new collection
     * 
     * @param callable $callback
     * @return void
     */
    public function walk()
    {
        return function ($callback) {
            array_walk($this->items, $callback);
        };
    }

    /**
     * Remove from the collection the given value
     * 
     * @param  mixed $value
     * @param  bool $removeFirstOnly
     * @return void
     */
    public function remove() 
    {
        return function ($value, bool $removeFirstOnly = false) {
            $this->items = Arr::remove($value, $this->items, $removeFirstOnly);
        };
    }
}
