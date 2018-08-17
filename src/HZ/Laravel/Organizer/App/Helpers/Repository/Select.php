<?php
namespace HZ\Laravel\Organizer\App\Helpers\Repository;

use Illuminate\Support\Collection;

class Select
{
    /**
     * list
     *
     * @param array
     */
    protected $list = [];

    /**
     * Constructor
     *
     * @param array $selectList
     */
    public function __construct(array $selectList)
    {
        $this->list = new Collection($selectList);
    }

    /**
     * Add key to list
     *
     * @param  string $key
     * @return $this
     */
    public function add(...$columns): self
    {
        $this->list = $this->list->merge($columns);

        return $this;
    }

    /**
     * Remove the given select column
     *
     * @param  mixed $column
     * @return $this
     */
    public function remove($column): self
    {
        $this->list->remove($column);
        
        return $this;
    }

    /**
     * Replace column
     *
     * @param  string $oldColumn
     * @param  mixed $columns
     * @return $this
     */
    public function replace($oldColumn, ...$columns)
    {
        $this->remove($oldColumn);

        $this->add(...$columns);

        return $this;
    }

    /**
     * Determine if the given column exists or not
     *
     * @param  string $column
     * @return bool
     */
    public function has(string $column): bool
    {
        return $this->list->contains($column);
    }

    /**
     * Return $list array
     *
     * @return array $list
     */
    public function list(): array
    {
        return $this->list->toArray();
    }

    /**
     * Determine if the select list is not empty
     * 
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return $this->list->isNotEmpty();
    }
}