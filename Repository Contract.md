# RepositoryInterface Contract

Interface path: `HZ\Illuminate\Mongez\Contracts\RepositoryInterface`

Any repository in `app/Repositories` should implement this interface.

# Table of contents 
- [Create](#create)
- [Update](#update)
- [Delete](#delete)
- [Has](#has)
- [Get](#get)
- [list](#list)

# Create 
The create method is responsible for creating the main entity and any related entities to it.

```php
/**
 * Create new repository item and store it in database
 * 
 * @param  \Illuminate\Http\Request $request
 * @return Illuminate\Database\Eloquent\Model
 */
public function create(Request $request): Model;
```

# Update
The update method is responsible for updating the main entity and any related entities to it.

```php
/**
 * Update repository item and store it in database
 * 
 * @param  int $id
 * @param  \Illuminate\Http\Request $request
 * @return Illuminate\Database\Eloquent\Model
 */
public function update(int $id, Request $request): Model;
```

# Delete 

Delete the repository entity and all of its related entities.

```php

    /**
     * Delete a specific record
     * 
     * @param  int id
     * @return bool
     */
    public function delete(int $id): bool;

```

# Has 

Determine if the given id exists in database.

```php

    /**
     * Determine whether the given id exists 
     * 
     * @param  int id
     * @return bool
     */
    public function has(int $id): bool;
```


# List
Retrieve records from database based on the given options as it could be anything like `select`, `filtering`, `ordering` ...etc.

```php

    /**
     * List of records
     * 
     * @param  array options
     * @return Illuminate\Support\Collection
     */
    public function list(array $option): Collection;
    
```

# Get
Get full details for the given id 

```php
    /**
     * Get a specific record with full details
     * 
     * @param  int id
     * @return \Item
     */
    public function get(int $id): Item;
    
```