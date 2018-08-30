<?php
namespace HZ\Laravel\Organizer\App\Contracts;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * Create new record
     * 
     * @param  \Illuminate\Http\Request $request
     * @return Illuminate\Database\Eloquent\Model
     */
    public function create(Request $request): Model;

    /**
     * Update a specific record
     * 
     * @param  int id
     * @param  \Illuminate\Http\Request $request
     * @return Illuminate\Database\Eloquent\Model
     */
    public function update(int $id, Request $request): Model;

    /**
     * Delete a specific record
     * 
     * @param  int id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * List of records
     * 
     * @param  array options
     * @return Illuminate\Support\Collection
     */
    public function list(array $option): Collection;
    
    /**
     * Get a specific record with full details
     * 
     * @param  int id
     * @return \HZ\Laravel\Organizer\App\Contracts\ItemInterface
     */
    public function get(int $id);
    
    /**
     * Determine whether the given id exists 
     * 
     * @param  int id
     * @return bool
     */
    public function has(int $id): bool;
}