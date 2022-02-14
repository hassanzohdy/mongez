<?php

namespace HZ\Illuminate\Mongez\Repository;

use Illuminate\Support\Collection;

interface RepositoryInterface
{
    /**
     * Create new record
     * 
     * @param  \Illuminate\Http\Request|array $data
     * @return Illuminate\Database\Eloquent\Model
     */
    public function create($data);

    /**
     * Update a the given record id or model
     * 
     * @param  int|model id
     * @param  \Illuminate\Http\Request|array $data
     * @return Illuminate\Database\Eloquent\Model
     */
    public function update($id, $data);

    /**
     * Delete a specific record
     * 
     * @param  int|Model id
     * @return bool
     */
    public function delete($id): bool;

    /**
     * Return List of records
     * 
     * @param  array options
     * @return Illuminate\Support\Collection
     */
    public function list(array $option): Collection;

    /**
     * Get a specific record with full details
     * 
     * @param  int id
     * @return mixed
     */
    public function get(int $id);

    /**
     * Determine whether the given value exists 
     * 
     * @param  mixed    $value
     * @param  string   $column
     * @return bool
     */
    public function has($value, string $column): bool;

    /**
     * Get the query handler
     *
     * @return mixed
     */
    public function getQuery();
}
