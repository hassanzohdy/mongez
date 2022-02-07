<?php

namespace HZ\Illuminate\Mongez\Repository;

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
     * Add one or more column to list
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
     * @param  mixed ...$newColumns
     * @return $this
     */
    public function replace($oldColumn, ...$newColumns): self
    {
        if (!$this->has($oldColumn)) return $this;

        $this->remove($oldColumn);

        $this->add(...$newColumns);

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
     * Return select list array
     *
     * @return array $list
     */
    public function list(): array
    {
        return $this->list->toArray();
    }

    /**
     * Determine if the select list is empty
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->list->isEmpty();
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
