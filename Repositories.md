# Repositories

The repository design pattern is a powerful strategy to map your data models and make it easier.


- [Repositories](#repositories)
- [What are repositories](#what-are-repositories)
- [Creating new repository](#creating-new-repository)
- [Repository methods](#repository-methods)
- [Creating database tables](#creating-database-tables)
  - [Create Method](#create-method)
  - [Update Method](#update-method)
  - [Has method](#has-method)
  - [Delete method](#delete-method)
  - [List method](#list-method)
  - [Get method](#get-method)
- [Repository Manager](#repository-manager)

# What are repositories

Repositories are your database handler for each `module` | `section` of your application.

For example, the `UsersRepository` is responsible for handling everything related to the user regarding the **CRUD** operations.

Every repository **MUST** implement the [Repository Interface](./repository-contract) to make your code more generic and has its own standards.

# Creating new repository

Now let's see how to create new repository and register it.

First create new file in `app/Modules/Users/Repositories/UsersRepository.php`.

```php
<?php 
namespace App\Repositories\Users;

use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository implements RepositoryInterface
{
}
```

> I alway prefer grouping files/classes in one directory, so if we've for example users and users groups, then it should be saved in `Users` directory for example.

Now go to `config/mongez.php` and in the `repositories` section add your **repository alias** as key and the repository class path as its value.

```php

    'repositories' => [
         'users' => App\Repositories\Users\UsersRepository::class,
    ],
```

The `users` key is used for easy access using the [repo()](./helper-functions#repo) function.

# Repository methods

Based on the [Repository Interface](./repository-contract) we've the following methods that should be implemented in every repository.

- [create](#create-method)
- [update](#create-method)
- [has](#get-method)
- [delete](#create-method)
- [list](#list-method)
- [get](#get-method)

# Creating database tables

Our main example here will be on users.

So let's assume we've the following tables in our database.

Users table
```
 id  int auto_increment PRIMARY 
 first_name varchar 
 last_name varchar
 email varchar UNIQUE
 password varchar
 image text
 created_at timestamp
 updated_at timestamp 
 deleted_at timestamp nullable INDEX
```

Permissions table

```
 id  int auto_increment PRIMARY 
 name varchar 
 route varchar
 method varchar
```

User permissions table
```
 id  int auto_increment PRIMARY 
 permission_id int INDEX
 user_id int INDEX
```

A quick example for repository usage is something like this

> I'm assuming that you're familiar with laravel relationships between models so the user model will `hasMany` of `permissions` and the permissions model will `belongsTo` the user for the entire example here. 

Now let's create our `UsersRepository.php` class in `apps/Repositories/Users`.


## Create Method

So let's start implementing the **create** method to store new user to database.

UsersRepository.php

```php
namespace App\Repositories\Users;

use App\Models\User\User;
use App\Models\User\Permission;
use Illuminate\Database\Eloquent\Model;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */ 
    public function create(Request $request): User
    {
        $user = new User;

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        
        if ($request->image) {
            $user->image = $request->image->store('images');
        }

        $user->save();

        if ($request->permissions) {
            foreach ($request->permissions as $permissionId) {
                $permission = Permission::find($permissionId);

                $user->permissions->save($permission);
            }
        }

        return $user;
    }
}
```

The [RepositoryInterface](./repository-contract) requires to return the `Model` object for both methods `create` and `update`.


But the `User` class is already an instance of `Model` so we can simply make it our return type and remove the `Model` class.

```php
namespace App\Repositories\Users;

use App\Models\User\User;
use App\Models\User\Permission;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */ 
    public function create(Request $request): User
    {
        $user = new User;

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        
        if ($request->image) {
            $user->image = $request->image->store('images');
        }

        $user->save();

        if ($request->permissions) {
            foreach ($request->permissions as $permissionId) {
                $permission = Permission::find($permissionId);

                $user->permissions->save($permission);
            }
        }

        return $user;
    }
}
```

Now we implemented the `create` method to create new user.

Once we've created our user record, then we checked if there are any permissions sent `array of permission ids`.


Now let's try this method on our controller

UsersController.php

```php

    /**
     * Create new user
     * 
     * @param  \Illuminate\Http\Request $request
    * @return \Response|string
    */
    public function store(Request $request) 
    {
        $usersRepository = repo('users');

        $user = $usersRepository->create($request);

        // continue your coding flow 
    }

```

Now let's see how to update user info.

## Update Method

Now let's implement the `update` method.

Add it after `create` method.

UsersRepository.php
```php
    /**
     * {@inheritDoc}
     */ 
    public function update(int $id, Request $request): User
    {
        $user = User::find($id);

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        
        if ($request->image) {
            $user->image = $request->image->store('images');
        }

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        // clear all permissions 
        $user->permissions->delete();

        if ($request->permissions) {
            foreach ($request->permissions as $permissionId) {
                $permission = Permission::find($permissionId);

                $user->permissions->save($permission);
            }
        }

        return $user;
    }
```

Same thing we did in the **create** method, we did it too in `update` method with some updates.

As in the updating user it may or may not change its password so we needed to check it.

And for the permissions, we've to delete all of the previous permissions to add the new ones or there will be a duplicate or incorrect permissions for the user.


Now let's call our **update** method 

UsersController.php
```php

    /**
     * Update user info
     * 
     * @param  \Illuminate\Http\Request $request
    * @param  int $Id
    * @return \Response|string
    */
    public function update(Request $request, int $id) 
    {
        $usersRepository = repo('users');

        $user = $usersRepository->update($id, $request);

        // continue your coding flow 
    }
```

## Has method

Wait!, we can't update user info before we make sure that the user exists in the first place.

So we'll check the user existence **In your controller for example** 

UsersRepository.php
```php
    /**
     * {@inheritDoc}
     */ 
    public function has(int $id): bool
    {
        return (bool) User::find($id);
    }
```

let's go back again to `UsersController.php` to update our `update` method there.

UsersController.php
```php

    /**
     * Update user info
     * 
     * @param  \Illuminate\Http\Request $request
    * @param  int $Id
    * @return \Response|string
    */
    public function update(Request $request, int $id) 
    {
        $usersRepository = repo('users');

        if (! $usersRepository->has($id)) {
            // return bad request 
        }

        $user = $usersRepository->update($id, $request);

        // continue your coding flow 
    }
```

## Delete method
So now we created our user and updated its info, now let delete it.

UsersRepository.php
```php
    /**
     * {@inheritDoc}
     */ 
    public function delete(int $id): bool
    {
        $user = User::find($id);

        // first remove user permissions
        $user->permissions->delete();

        // now remove user record
        $user->delete();

        return true;
    }
```

So we deleted our user record and his permissions as well so our database now is clean from that user :D.

Now let's implement our destroy method on our controller


UsersController.php
```php

    /**
     * Delete user
     * 
    * @param  int $Id
    * @return \Response|string
    */
    public function destroy(int $id) 
    {
        $usersRepository = repo('users');

        $usersRepository->delete($id);

        // continue your coding flow 
    }
```

## List method
List method is considered to be our primary method as it will be the mostly used method in the repository in general.

This method is used to `fetch` | `retrieve` | `get` set of records based on the given options to it.

It should cover all kinds of filtrations, pagination, custom selection and ordering.

Let's Stop talking and start coding.


This time i will write the method call then the implementation code


UsersController.php
```php
    /**
     * Get users list
     * 
     * @param  \Illuminate\Http\Request $request
    * @return \Response|string
     */
    public function index(Request $request)
    {
        $usersRepository = repo('users');

        $users = $usersRepository->list([
            'select' => ['id', 'name', 'email'],   
            'orderBy' => [$request->order_by, $request->sort],
        ]);
    }
```

UsersRepository.php
```php

namespace App\Repositories\Users;

use App\Models\User\User;
use Illuminate\Support\Arr;
use App\Models\User\Permission;
use Illuminate\Support\Collection;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */ 
    public function list(array $options): Collection
    {
        // Let's create our query object
        $query = new User;
        
        // adjust our filtering 
        if (! empty($options['select'])) {
            $select = $options['select'];

            // pass our select columns to the select method
            $query->select(...$select);
        }
    }
```

Because this method will have more options than the others, so i will chunk its code to make it easier to follow and not making thing get messy :D.

Now we added two more classes in the `use` section.
The `Collection` as it is going to be our return type of the method.

The `Arr` class that we will use it later in this example.

So far we just created new object of our user model then checked if there is a `select` option is passed to our options list and if it is not empty then we will send it directly to the select method

In our controller, we passed the `select` options with the given array of `['id', 'name', 'email']` which is fine by now.

But if we executed our query with the given selected columns it will throw an error to tell us that the column `name` is not found in our users table.

The `name` here is meant to be the concatenation of the `first_name` and `last_name` so we need to do that in our list method.


UsersRepository.php
```php

    /**
     * {@inheritDoc}
     */ 
    public function list(array $options): Collection
    {
        // Let's create our query object
        $query = new User;
        
        // adjust our filtering 
        if (! empty($options['select'])) {
            $select = $options['select'];

            // check if the name value is in the select array
            if (in_array('name', $select)) {
                $select = Arr::remove($select, 'name');

                $query->selectRaw('CONCAT(first_name, " ", last_name) as name');
            }

            // add the rest of the select columns
            $query->select(...$select);
        }
    }
```

Now we checked if the name is in our array, then we will remove it using the [Arr::remove()](./helpers/arr#remove) method.

then we made our concatenation between the first and the last name using the `selectRaw` method.

Now let's go for the order by part.

UsersRepository.php
```php

    /**
     * {@inheritDoc}
     */ 
    public function list(array $options): Collection
    {
        // Let's create our query object
        $query = new User;
        
        // adjust our filtering 
        if (! empty($options['select'])) {
            $select = $options['select'];

            // check if the name value is in the select array
            if (in_array('name', $select)) {
                $select = Arr::remove($select, 'name');

                $query->selectRaw('CONCAT(first_name, " ", last_name) as name');
            }

            // add the rest of the select columns
            $query->select(...$select);
        }


        // order by  
        // default order is by id from new to old 
        $column = 'id';
        $direction = 'DESC';

        // it should be sent like:
        // 'orderBy' => ['id', 'DESC']
        if (! empty($options['orderBy'])) {
            list($column, $direction) = $options['orderBy'];
        }
        
        $query->orderBy($column, $direction);

        return $query->get();
    }
```

So we set our default order by column and direction then checked if the order by key is sent to our options then last step is to `get` the records.

## Get method 

The `get` method differs from the `list` method that the list method send minimal info about the user and it also sends many records, unlike the `get` method which retrieves one user only but with full details.


UsersRepository.php
```php
namespace App\Repositories\Users;

use App\Models\User\User;
use Illuminate\Support\Arr;
use App\Models\User\Permission;
use Illuminate\Support\Collection;
use App\Items\User\User as UserItem;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */ 
    public function get(int $id): UserItem
    {
        $user = User::find($id);

        return new UserItem($user);
    }
```

> We added the `UserItem` as its required as a return type for the `get` method, see [RepositoryInterface](./repository-contract). 

Learn more about Items [here](./items).

A very simple `get` method, we will get the uer object then send it to our User Item object to handle it 

We need from this user `id`, `email`, `name`, `full image url` and `array of permission ids`.

SO let's head to our User Item class in `app/Items/User/User.php`

```php
namespace App\Items\User;

use HZ\Illuminate\Mongez\Managers\Item;

class User extends Item 
{
    /**
     * Set the data that will be returned in the json response
     * 
     * @return array
     */
    public function send(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->first_name . ' ' . $this->last_name,
            'image' => url($this->image),
            'permissions' => $this->permissions->pluck('permission_id'), // the permissions property will fetch the user permissions from its model
        ];
    }
}
```


And that's it. now our User details is ready to be sent.

UsersController.php
```php
    /**
     * Get user details
     * 
     * @param int $Id
     * @return \Response|string
     */
    public function show(int $id)
    {
        $usersRepository = repo('users');

        if (! $usersRepository->has($id)) {
            // return bad request or not found error
        }

        $user = $usersRepository->get($id);

        // to get the info we made
        $userDetails = $user->send();
    }
```

Mainly the `send` method is automatically called in json responses so you won't need to call the `send` method. 

UsersApiController.php
```php
    /**
     * Get user details
     * 
     * @param int $Id
     * @return json
     */
    public function show(int $id)
    {
        $usersRepository = repo('users');

        if (! $usersRepository->has($id)) {
            // return bad request or not found error
        }

        $user = $usersRepository->get($id);

        return response()->json([
            'user' => $user,
        ]);
    }
```

# Repository Manager
There is a powerful `Manager` for repositories that is shipped with the package, the [RepositoryManager](./repository-manager) which will make your repositories extremely managed.**** 