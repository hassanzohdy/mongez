<?php
namespace App\Contracts;

use Item;
use Collection;
use Request;
use Model;

interface RepositoryInterface
{
    /**
     * Create new record
     * 
     * @param  \Request $request
     * @return \Model
     */
    public function create(Request $request): Model;

    /**
     * Update a specific record
     * 
     * @param  int id
     * @param  \Request $request
     * @return \Model
     */
    public function update(int $id, Request $request): Model;

    /**
     * Delete a specific record
     * 
     * @param int id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * List of records
     * 
     * @param array options
     * @return collection
     */
    public function list(array $option): Collection;
    
    /**
     * Get a specific record with full details
     * 
     * @param int id
     * @return \Item
     */
    public function get(int $id): Item;
    
    /**
     * Determine whether the given id exists 
     * 
     * @param int id
     * @return bool
     */
    public function has(int $id): bool;
}