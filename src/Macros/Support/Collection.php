<?php
namespace HZ\Illuminate\Mongez\Macros\Support;

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
     * Add one ore element to the beginning of the collection
     * 
     * @param  mixed ...$value
     * @return void
     */
    public function unshift() {
        return function (...$value) {
            array_unshift($this->items, ...$value);
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

    /**
     * Remove from the collection the given value 
     * 
     * @param  string $arrayKey
     * @param  mixed $value
     * @param  bool $removeFirstOnly
     * @return void
     */
    public function removeWhere() 
    {
        return function ($arrayKey, $value, bool $removeFirstOnly = false) {
            foreach ($this->items as $key => $item) {
                $oldValue = $item;
                $item = (array) $item;

                if(isset($item[$arrayKey]) && $item[$arrayKey] == $value) {
                    unset($this->items[$key]);
                } else {
                    $this->items[$key] = $oldValue;
                }
            }
        };
    }
}