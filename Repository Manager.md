# RepositoryManager

The `RepositoryManager` will make your repositories much easier to control and manage as it will do for you the hard work and leave only simple structure to follow/implement.

Don't forget to read the [Repositories](./repositories) tutorial before continue.

As every repository should `implement` the [RepositoryInterface](./repository-contract), our manager will do most of required methods for you and leave only one method [RepositoryInterface::get()](./repository-contract#get) for you to handle it by yourself.


Now let's get started to see some real code in action.

- [RepositoryManager](#repositorymanager)
- [RepositoryTrait](#repositorytrait)
- [Users groups](#users-groups)
- [Usage](#usage)
- [Creating users repository](#creating-users-repository)
- [Managing the Create/Update methods](#managing-the-createupdate-methods)
    - [Example](#example)
- [DATA Constant](#data-constant)
    - [Example](#example-1)
- [Deleting records](#deleting-records)
- [The `get` method](#the-get-method)
- [The `has` method](#the-has-method)
- [Listing](#listing)
    - [The query builder](#the-query-builder)
    - [List constants](#list-constants)
    - [Retrieval Mode](#retrieval-mode)
        - [Using soft delete](#using-soft-delete)
    - [Handling Selects](#handling-selects)
    - [Filtering records](#filtering-records)
    - [Ordering records](#ordering-records)
    - [Managing Records](#managing-records)
- [Final repository](#final-repository)
- [Available Methods](#available-methods)
- [setData](#setdata)
- [onCreate](#oncreate)
- [onUpdate](#onupdate)
- [onSave](#onsave)
- [updateModel](#updatemodel)
- [findOrCreate](#findorcreate)
    - [Example](#example-2)
- [setModelData](#setmodeldata)
    - [Example](#example-3)
- [beforeDeleting](#beforedeleting)
- [onDelete](#ondelete)
- [option](#option)
- [select](#select)
- [filter](#filter)
- [orderBy](#orderby)
- [column](#column)
- [records](#records)
- [Available Properties](#available-properties)
    - [Example](#example-4)
        - [The `$this->table` property](#the-this-table-property)
        - [The `$this->query` property](#the-this-query-property)
- [Available Constants](#available-constants)
- [Macroable](#macroable)


# RepositoryTrait

By default the [RepositoryTrait](./repository-trait) is injected in the repository manager, so you can easily access any other repository by calling it's alias name directly.

For example:

```php

$usersRepository = $this->users;
$postsRepository = $this->posts;

```


# Users groups
In this tutorial we will add one more simple table to clarify how to use options with more readable filtering.

`user_groups` table
```
id int auto_increment PRIMARY
name varchar(64)
```

So every user has its own group so there will be one more column in the `users` table called `user_group_id`.

# Usage

So let's take the same example in our [Repository tutorial](./repositories#creating-new-repository) and see how to implement it with the repository manager.

# Creating users repository

First create new file in `app/Repositories/Users/UsersRepository.php`.

```php
<?php 
namespace App\Repositories\Users;

use HZ\Illuminate\Mongez\Managers\RepositoryManager;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
}
```

> For lazy people like me xD, you can use the repository manager class alias `RepositoryManager` directly by adding it to the aliases list.

> Check my preferred aliases list [here](./home#classes-aliasing).

# Managing the Create/Update methods

The manager will implement both methods `create` and `update` but in a neat way.

You must define your **model** class name in the repository using the `const MODEL` in the beginning of your class. 

So the flow of both methods works like this:

- Gets the object model (creating new one on create) and (using the `find` method on update).
- Calls the [setData()](#setdata) method to set common data between both methods.
- Saves the model using the `save()` method.
- Triggers the [onCreate()](/#onCreate) | [onUpdate()](#onUpdate) method to do any post actions after the successful transaction.
- Triggers the [onSave()](/#onSave) method to handle any common **between create and update** post actions after the successful transaction.

- Returns the model object.

> Please note that the `setData()` method is abstract, therefore it has to be implemented in every child repository.

## Example

OK now let's to create and update user based on our main example.

```php
<?php
namespace App\Repositories\Users;

use App\Models\User\User;
use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\RepositoryManager;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    const MODEL = User::class;

    /**
     * {@inheritDoc}
     */
    protected function setData(User $user, Request $request)
    {
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        
        if ($request->image) {
            $user->image = $request->image->store('images');
        }

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
    } 
}
```

So far we added our model class and implemented the `setData` method to set our info in both create and update methods. 

As we're not going to do any special thing after creating or updating the model but handling the `permissions`, so we'll use the `onSave` method to handle it for us.

```php
    /**
     * {@inheritDoc}
     */ 
    protected function onSave(User $user, Request $request)
    {
        // clear all permissions 
        $user->permissions->delete();

        if ($request->permissions) {
            foreach ($request->permissions as $permissionId) {
                $permission = Permission::find($permissionId);

                $user->permissions->save($permission);
            }
        }
    }
```

We will remove any permissions in both transactions, the insert and the update as if we're inserting new user, then the database won't complain from the not found records to be deleted.

# DATA Constant
version `1.2`.

A new constant is available `DATA`.

In simple words what does this constant do is you pass an array to it so it will automatically grab the data from the request based on the keys and set it to the model.    

## Example
Let's take the same previous example, as there are three columns its values will be taken directly from the request object.

```php
<?php
namespace App\Repositories\Users;

use App\Models\User\User;
use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\RepositoryManager;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    const MODEL = User::class;

    /**
     * {@inheritDoc}
     */
    const DATA = [
        'first_name', 'last_name', 'email'
    ];

    /**
     * {@inheritDoc}
     */
    protected function setData(User $user, Request $request)
    {
        if ($request->image) {
            $user->image = $request->image->store('images');
        }

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
    } 
}
```

> Please note that this constant works only with simple direct fields.

# Deleting records

The repository manager will delete the record from database for the given id, so the `const MODEL` also required here to delete the record.

There are two triggers here happen for deleting, the `beforeDeleting` method and the `onDelete` method.

let's say we want to remove the user permissions before deleting the user itself.


```php
/**
 * {@inheritDoc}
 */
protected function beforeDeleting(User $user)
{
    $user->permissions->delete();
} 
```

# The `get` method
This is the only `unimplemented` method in the [Repository Contract](./repository-contract) methods as it should return a full details about the user/model so it's up to you to implement it.

So all what we need to add for the moment is only the method implementation.

> Please note that the `get` method returns an [Item](./items), so we will create an empty user item in the `app/Items` directory that extens the **Item** class.

```php

namespace App\Repositories\Users;

use App\Models\User\User;
use Illuminate\Http\Request;
use App\Items\User\User as UserItem;
use HZ\Illuminate\Mongez\Managers\RepositoryManager;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */ 
    public function get(int $id): UserItem
    {
        $user = (static::MODEL)::find($id);

        return new UserItem($user);
    }
```

# The `has` method
This method is implemented by the manager so you don't have to re-implement it for basic checking.


# Listing 
Now let's head to the biggy method here, the `list` method.

This method can/should accept various types of passed options to it and it's really implemented in the repository manager to make it much simpler.


## The query builder

In the list method it runs using the query builder so you can call the [query builder](https://laravel.com/docs/5.6/queries) methods dynamically.

You can also use the query builder object in any method related to the `list` method like `RepositoryManager::select` or `RepositoryManager::filter` method using the query property `$this->query->...` .

So let's you want to do a where clause while yuo're implementing the `select` method then you could do it in two ways:

1- Dynamic way

```php

protected function select() 
{
    $this->where('name' , 'LIKE', '%my-name%');
}
```

2- Direct way

```php

protected function select() 
{
    $this->query->where('name' , 'LIKE', '%my-name%');
}
```

## List constants

The list method can be handled with some constants to make it easier for you to manage it.

- `TABLE`: **String** required , set the main table name
- `TABLE_ALIAS`: **String** optional, set the table alias name to be used instead of table name as it makes it easier if the query has many joins.
- `USING_SOFT_DELETE`: **Bool** optional default `true`, if yuo're using soft deletes in your models then this constant should be set to `true`. 
- `RETRIEVAL_MODE`: **String** optional, by default the value of this constant is `self::RETRIEVE_ACTIVE_RECORDS` which means it will retrieve only the `undeleted` records, you'can override it when you pass the options to the list method, [see this example](#retrieval-mode).
  

## Retrieval Mode
Using [soft deleting](https://laravel.com/docs/5.6/eloquent#soft-deleting) is handled here pretty simple and here are some examples for it.

### Using soft delete 
By default all repositories will be treated as it's using soft deletes which you can enable/disable it by the defining the `USING_SOFT_DELETE` constant.
 
```php
    /**
     * {@inheritDoc}
     */
    const USING_SOFT_DELETE = true;
```

As we said this is the default value for using soft deletes, if you set it to false then you don't have to read the rest of this section.

If you're using different column than the `deleted_at`, then you may specify it too.

```php
    /**
     * {@inheritDoc}
     */
    const USING_SOFT_DELETE = true;

    /**
     * {@inheritDoc}
     */
    const DELETED_AT = 'deleted_at';
```

Every time the `list` method is called only the **active** `un-deleted` records will be retrieved as we will run the `whereNull` at the beginning of the method before anything else.

You may change what type of records you want to get, as you may want to get all/active/deleted records based on your use case.

You can of course override all of the above settings when you call the `list` method.

```php

use RepositoryManager;

$usersRepository = repo('users');


// get only active records ---> default
$users = $usersRepository->list([
    'select' => ['id', 'name', 'email', 'created_at'],
    'limit' => 30,
    'page' => 1,
]);

// get all records
$users = $usersRepository->list([
    'select' => ['id', 'name', 'email', 'created_at'],
    'limit' => 30,
    'page' => 1,
    RepositoryManager::RETRIEVE_MODE = > RepositoryManager::RETRIEVE_ALL_RECORDS,
]);


// get only deleted records
$users = $usersRepository->list([
    'select' => ['id', 'name', 'email', 'created_at'],
    'limit' => 30,
    'page' => 1,
    RepositoryManager::RETRIEVE_MODE = > RepositoryManager::RETRIEVE_DELETED_RECORDS,
]);
```

You may also set the default retrieval mode using the `DEFAULT_RETRIEVAL_MODE` constant in your repository.

So the full default values for the entire retrieval mode is:

```php
    /**
     * {@inheritDoc}
     */
    const USING_SOFT_DELETE = true;

    /**
     * {@inheritDoc}
     */
    const DELETED_AT = 'deleted_at';

    
    /**
     * {@inheritDoc}
     */
    const DEFAULT_RETRIEVAL_MODE = self::RETRIEVE_ACTIVE_RECORDS;
```

> Don't forget to index your `deleted_at` column for performance.


Let's recap it all together, so far and see how should your repository look like for default mode.


UsersRepository.php
```php
<?php
namespace App\Repositories\Users;

use App\Models\User\User;
use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\RepositoryManager;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    const MODEL = User::class;

    /**
     * {@inheritDoc}
     */
    const TABLE = 'users';

    /**
     * {@inheritDoc}
     */
    protected function setData(User $user, Request $request)
    {
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        
        if ($request->image) {
            $user->image = $request->image->store('images');
        }

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
    } 
}
```

## Handling Selects

Passing column names to the repository to get it would be little ugly and would make you update your code each time you modify your database.

So let's take an example for the normal may and the preferred way.

Let's assume we want to get the following data:
- user id
- user name (first and last name concatenated)
- user email
- user group name

So in a normal way you would make a join with the `user_groups` table to get the user group name, your query will be something like:

```php

$users = DB::table('users')->join('user_groups' 'users.user_group_id', '=', 'user_groups.id')->select('users.id', 'users.email', 'user_groups.name as `group`')->selectRaw('CONCAT(users.first_name, " ", users.last_name) as `name`')->get();
```

If we're going to use our repository list method then the method call will be something like this:

```php

$users = $usersRepository->list([
    'select' => ['users.id', 'users.email', 'user_groups.name as `group`', DB::raw('CONCAT(users.first_name, " ", users.last_name) as `name`')],
]);

```

Seems to be more harder each time you want to get the users with different columns selection.

Now let's see how we're going to do it using the repository manager.

First let's see how we will call our method:

```php

$users = $usersRepository->list([
    'select' => ['id', 'name', 'email', 'group'],
]);

```

Very simple and neat, right? That's the main purpose here for the repositories in general and the repository manager particularly.

Let's see how to implement it in our `UsersRepository`.

UsersRepository.php
```php
<?php
namespace App\Repositories\Users;

use App\Models\User\User;
use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\RepositoryManager;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    const TABLE = 'users';
    
    /**
     * {@inheritDoc}
     */
    const TABLE_ALIAS = 'u';

    /**
     * {@inheritDoc}
     */
    protected function select()
    {
        // Append all columns that don't need any work on it like, id and email
        foreach (['id', 'email', 'user_group_id'] as $column) {
            $this->select->replace($column, $this->column($column));
        }
    } 
}
```

Now let's see what have we done here.

- We set our table name using the `TABLE` constant.
- We set our table alias name using the `TABLE_ALIAS` constant.

Next we need to implement the `select` method as it is an `abstract` method in the repository manager.

Once we set our table alias name then it will be used in the entire query instead of the table name.

So if we want to make a `where` condition for the id we may write it like this:

```php
$this->where('u.id', 763);
```

But you may use the `column()` method to define the proper table name for use like this:

```php
$this->where($this->column('id'), 763);
```

Now let's go back to our code.

Once the `list` method is called, a [Select](./select-helper) class is instantiated to handle the select array that is passed to the list method.

So what we've did so far is we replaced our main columns that may or may not be selected to append it with the table name, which in this case will be the `u` alias.

The [Select::replace()](./select-helper#replace) will check first if the given column exists in the select or not, if not then it will skip the replacement.

Now let's continue.


UsersRepository.php
```php
<?php
namespace App\Repositories\Users;

use App\Models\User\User;
use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\RepositoryManager;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    const TABLE = 'users';
    
    /**
     * {@inheritDoc}
     */
    const TABLE_ALIAS = 'u';

    /**
     * {@inheritDoc}
     */
    protected function select()
    {
        // Append all columns that don't need any work on it like, id and email
        foreach (['id', 'email', 'user_group_id'] as $column) {
            $this->select->replace($column, $this->column($column));
        }

        // check if the `name` key is passed to the select list
        if ($this->select->has('name')) {
            $this->select->remove('name');
            $this->select->add($this->raw('CONCAT(u.first_name, " ", u.last_name) as name'));

            // or we could do it in one method using the replace method
            $this->select->replace('name', $this->raw('CONCAT(u.first_name, " ", u.last_name) as name'));
        }
    } 
}
```

If the `name` value is sent to the `select` list, then we will replace it by removing it from the list and add the corresponding database sql for it.

Now let's manage the `group` value to get the user group name.


UsersRepository.php
```php
<?php
namespace App\Repositories\Users;

use App\Models\User\User;
use Illuminate\Http\Request;
use HZ\Illuminate\Mongez\Managers\RepositoryManager;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    const TABLE = 'users';
    
    /**
     * {@inheritDoc}
     */
    const TABLE_ALIAS = 'u';

    /**
     * {@inheritDoc}
     */
    protected function select()
    {
        // Append all columns that don't need any work on it like, id and email
        foreach (['id', 'email', 'user_group_id'] as $column) {
            $this->select->replace($column, $this->column($column));
        }

        // check if the `name` key is passed to the select list
        if ($this->select->has('name')) {
            $this->select->remove('name');
            $this->select->add($this->raw('CONCAT(u.first_name, " ", u.last_name) as name'));

            // or we could do it in one method using the replace method
            $this->select->replace('name', $this->raw('CONCAT(u.first_name, " ", u.last_name) as name'));
        }

        // check if the `group` value is sent to the select list
        if ($this->select->has('group')) {
            $this->select->remove('group');

            // now let's make a join with the user groups table
            $this->join('user_groups as ug', 'ug.id', '=', 'u.user_group_id');

            $this->select->add('ug.name as `group`');
        }
    } 
}
```

The same we did in the `name` value, we did it again for the group value but this time we made a join with the `user_groups` table.

Don't forget that you can call the query builder methods dynamically without calling the `$this->query` property.

> Remember that the `group` key is a reserved keyword in mysql, so we need to escape it by encapsulating it using the backtick `.   

> This method is called inside the `list` method that is implemented in the repository manager so it will be automatically.

## Filtering records

We need a method to implement our `wheres` conditions to filter our returned records, so we need to implement the `filter` method as its an abstract method in the repository manager.

Let's say we want to get users for certain group(s) or search for user by email(s).

```php

// filter by user group id
$users = $usersRepository->list([
    'select' => ['id', 'name', 'emai', 'group'],
    'groupId' => $request->group_id,
]);


// or we may filter by email

// filter by user email
$users = $usersRepository->list([
    'select' => ['id', 'name', 'emai', 'group'],
    'email' => $request->email,
]);
```

Now let's go back to our repository and implement the filter method

UsersRepository.php

```php

    /**
     * {@inheritDoc}
     */
    protected function filter() 
    {
        // filter by user group id
        if ($userGroupId = $this->option('groupId')) {
            $this->where($this->column('user_group_id', $userGroupId));
        }

        // filter by email
        if ($email= $this->option('email')) {
            $this->whereLike($this->column('email', $email));
        }
    }  
```

Now we filtered the records using user group id, if it was passed
and/or with email if it was passed as an option.

The [option()](#option) is used to get any key passed to the `list` method.

> Please note the `whereLike` method is not in laravel query builder by default, but it is implemented in the [Macro Query builder](./macro-query#wherelike). 

## Ordering records
Now lets see how to implement the `orderBy` method.

This method should be used to handle records sorting.

By default the manager will sort records `id DESC` unless you override this.

So let's see how it works in action

someFile.php
```php

$users = $usersRepository->list([
    'select' => ['id', 'name', 'emai', 'group'],
    'groupId' => $request->group_id,
    'orderBy' => [$request->order_by, $request->order_direction],
]);

```

Now let's go to our repo to handle this ordering section.

UsersRepository.php
```php
    /**
     * {@inheritDoc}
     */
     protected function orderBy(array $orderBy)
     {
        if (empty($orderBy)) {
            $orderBy = [$this->column('id'), 'DESC'];
        }

        $this->query->orderBy(...$orderBy);
     }
```

This is what happens in the manager, but what if we've something like:


someFile.php
```php

$users = $usersRepository->list([
    'select' => ['id', 'name', 'emai', 'group'],
    'groupId' => $request->group_id,
    'orderBy' => ['name', 'DESC'],
]);

```

Here we want to order our users by the user name or even by user group name, so that order by method won't work correctly as we don't have a `name` column in the users table.


UsersRepository.php
```php
    /**
     * {@inheritDoc}
     */
     protected function orderBy(array $orderBy)
     {
        if (empty($orderBy)) {
            $orderBy = [$this->column('id'), 'DESC'];
        } else {
            list($column, $direction) = $orderBy;

            if ($column == 'name') {
                $column = 'u.first_name';
            } elseif ($column == 'group') {
                $column = 'ug.name';
            }
        }

        $this->query->orderBy(...$orderBy);
     }
```

So first thing we did here is we split our order by array into two variables, the column name and the direction.

So if the column name is `name` then we will replace it with `u.first_name` as this is what we've in our table.

Same applies on the `group` name, we replaced it with `ug.name` as the users table doesn't have column name called `group` nor the users group table itself but `ug.name`.

Remember, we made a join earlier in the select clause so we already have the user group alias name `ug`.


## Managing Records
Now let's go to the final part of this tutorial which is managing records.

This method will be triggered after the query is executed and returned the records from the database.

Sometimes we want to map these records to do some operations on the returned records.

Let's say we've in our users table `image` fields which holds the relative image path to the user, but we need to get the full absolute path for the image.

UsersRepository.php

```php

namespace App\Repositories\Users;

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use HZ\Illuminate\Mongez\Managers\RepositoryManager;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    protected function records(Collection $records): Collection
    {
        // set an absolute image for every user
        return $records->map(function ($user) {
            if (! empty($user->image)) {
                $user->image = url($user->image);
            }

            return $user;
        });
    } 
}
```

# Final repository

Here is the final repository for all operations we've done in this tutorial


```php

namespace App\Repositories\Users;

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Items\User\User as UserItem;
use HZ\Illuminate\Mongez\Managers\RepositoryManager;
use HZ\Illuminate\Mongez\Contracts\RepositoryInterface;

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    const MODEL = User::class;

    /**
     * {@inheritDoc}
     */
    const TABLE = 'users';
    
    /**
     * {@inheritDoc}
     */
    const TABLE_ALIAS = 'u';

    /**
     * {@inheritDoc}
     */
    protected function setData(User $user, Request $request)
    {
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        
        if ($request->image) {
            $user->image = $request->image->store('images');
        }

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
    } 

    /**
     * {@inheritDoc}
     */ 
    protected function onSave(User $user, Request $request)
    {
        // clear all permissions 
        $user->permissions->delete();

        if ($request->permissions) {
            foreach ($request->permissions as $permissionId) {
                $permission = Permission::find($permissionId);

                $user->permissions->save($permission);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function select()
    {
        // Append all columns that don't need any work on it like, id and email
        foreach (['id', 'email', 'user_group_id'] as $column) {
            $this->select->replace($column, $this->column($column));
        }

        // check if the `name` key is passed to the select list
        if ($this->select->has('name')) {
            $this->select->remove('name');
            $this->select->add($this->raw('CONCAT(u.first_name, " ", u.last_name) as name'));

            // or we could do it in one method using the replace method
            $this->select->replace('name', $this->raw('CONCAT(u.first_name, " ", u.last_name) as name'));
        }

        // check if the `group` value is sent to the select list
        if ($this->select->has('group')) {
            $this->select->remove('group');

            // now let's make a join with the user groups table
            $this->join('user_groups as ug', 'ug.id', '=', 'u.user_group_id');

            $this->select->add('ug.name as `group`');
        }
    } 
    
    /**
     * {@inheritDoc}
     */
    protected function filter() 
    {
        // filter by user group id
        if ($userGroupId = $this->option('groupId')) {
            $this->where($this->column('user_group_id', $userGroupId));
        }

        // filter by email
        if ($email= $this->option('email')) {
            $this->whereLike($this->column('email', $email));
        }
    }  

    /**
     * {@inheritDoc}
     */
     protected function orderBy(array $orderBy)
     {
        if (empty($orderBy)) {
            $orderBy = [$this->column('id'), 'DESC'];
        } else {
            list($column, $direction) = $orderBy;

            if ($column == 'name') {
                $column = 'u.first_name';
            } elseif ($column == 'group') {
                $column = 'ug.name';
            }
        }

        $this->query->orderBy(...$orderBy);
     }

    /**
     * {@inheritDoc}
     */
    protected function records(Collection $records): Collection
    {
        // set an absolute image for every user
        return $records->map(function ($user) {
            if (! empty($user->image)) {
                $user->image = url($user->image);
            }

            return $user;
        });
    } 

    /**
     * {@inheritDoc}
     */ 
    public function get(int $id): UserItem
    {
        $user = (static::MODEL)::find($id);

        return new UserItem($user);
    }
}
```


# Available Methods
- [setData](#setData)
- [onCreate](#onCreate)
- [onUpdate](#onUpdate)
- [onSave](#onSave)
- [updateModel](#updateModel)
- [beforeDeleting](#beforeDeleting)
- [onDelete](#onDelete)
- [option](#option)
- [select](#select)
- [filter](#filter)
- [orderBy](#orderBy)
- [column](#column)
- [records](#records)
- [first](#records)

# setData
`abstract setData(\Illuminate\Database\Eloquent\Model $model, \Illuminate\Http\Request $request): void`

Set data to the model.

This method is triggered on create and update as it will be a useful method to set model data once instead of adding it on create and adding it again on update.

> This is an abstract method, it has to be implemented in every repository.


# onCreate
`onCreate(\Illuminate\Database\Eloquent\Model $model, \Illuminate\Http\Request $request): void`

This method will be triggered after creating new record in database.

# onUpdate
`onCreate(\Illuminate\Database\Eloquent\Model $model, \Illuminate\Http\Request $request): void`

This method will be triggered after record is updated.

# onSave
`onCreate(\Illuminate\Database\Eloquent\Model $model, \Illuminate\Http\Request $request): void`

This method will be triggered after creating or updating.

# updateModel
`updateModel(\Illuminate\Database\Eloquent\Model $model, array $columns): void`

Update record for the given model.

This is a very helpful method to update the given model and pass an array of columns or key/value pairs to be set to the model.

Let's see some examples on how to use it.

```php
    /**
     * {@inheritDoc}
     */  
    protected function onSave($user, $request)
    {
        // now model has been saved 
        // so let's save some other models 
        // let's assume we want save user profile settings directly from the request

        // let's assume the profileSettings will have the following names
        // [job, education, age, mobile, country, city, state]
        // so these names are stored in array called profile_settings
        $profileSettings = $request->profile_settings;

        // now we will add the user id to array
        $profileSettings['user_id'] = $user->id;

        $userProfileModel = new UserProfile;

        // pass the model and the data that will be saved in database
        // what happens in the method internally, it loops through the given `key/value` array and assign each key to the model for its value.
        $this->updateModel($userProfileModel, $profileSettings);

        // another way to update model

        // lets say that the previous profile settings were not attached to `profile_settings` so we will access it directly through the $_POSt, for example $age = $request->age; instead of $age = $request->profile_settings['age'];

        // normal indexed array
        $profileSettings = ['job', 'education', 'age', 'mobile', 'country', 'city', 'state'];

        $this->updateModel($userProfileModel, $profileSettings);

        // But wait!, we forgot to assign the user id to that indexed array, well no problem, we can pass combined indexed and associated array to the method and it will handle it too.
        $profileSettings['user_id'] = $user->id;
        
        $this->updateModel($userProfileModel, $profileSettings);        
    }
```

# findOrCreate
`findOrCreate(string $model, int $id): Model`

Version: `1.2`

Find or get empty model if the given id is not found in the model.

## Example

```php
/**
 * {@inheritDoc}
 */ 
protected function OnSave(User $model, Request $request)
{
    foreach ($request->permissions as $permission) {
        $permissionModel = $this->findOrCreate(PermissionModel::class, $permission['id']);

        $permissionModel->name = $permission['name'];
    }
}
```


# setModelData
`setModelData(Model $model, array $data)): void`

Version: `1.2`

Set the given data to the model directly.

## Example

```php
// setting data to a model with normal way is like this
$userModel->first_name = $request->first_name;
$userModel->last_name = $request->last_name;
$userModel->email = $request->email;
$userModel->birthdate = $request->birthdate;

// with the setModelData method
$this->setModelData($userModel, $request->only(['first_name', 'last_name', 'email', 'birthdate']));

```

# beforeDeleting
`beforeDeleting(\Illuminate\Database\Eloquent\Model $model): void`

This method is triggered before deleting the model.

# onDelete

`onDelete(\Illuminate\Database\Eloquent\Model $model, int $id): void`

This method is triggered after deleting the model.

> The id is passed as second argument.

# option

`option(string $key, $default = null): mixed`

Get value from the passed options to the `list` method

If the option doesn't exists in options list, return the default value

# select
`abstract select(): void`

This method is responsible for handling everything related to the select clause.

> This is an abstract method, it has to be implemented in every repository.

> This method is triggered after creating new object of the query builder.

# filter
`abstract filter(): void`

This method is responsible for handling everything related to filtering records.

> This is an abstract method, it has to be implemented in every repository.

> This method is triggered after handling the [Soft deleting](#retrieval-mode).

# orderBy
`orderBy(array $orderByOptions): void`

This method is responsible for handling the order by clause.


# column
`column(string $column): string`

Get the column name appended with the table/alias.

# records

`records(\Illuminate\Support\Collection $records): \Illuminate\Support\Collection`

Adjust records that were fetched from database

This method is triggered after retrieving records from database in `list` method.


# Available Properties

The following properties are available in any method related to the `list` method.

- `query` Contains the query builder object
- `table` Contains the table or table alias name.


## Example

```php

    /**
     * {@inheritDoc}
     */
    protected function orderBy(array $orderBy)
    {
        $this->query->orderBy($this->table . '.id', 'DESC');
    } 
```

### The `$this->table` property

If you set a value to the `TABLE_ALIAS` constant, then it will be the value of that constant, otherwise it will be the value of `TABLE` constant. 

### The `$this->query` property

Because this class has `orderBy` method, so we can't call it dynamically as it will lead us to infinity loop of call stack.

In this case, we need to set it directly to the `query` property.

> Don't forget that there is a `select` method in the repository manager, so don't try to use `$this->select(...$columns)` to set columns, otherwise use the `Select` class as illustrated earlier.


# Available Constants

| Constant        | Type     | For                  | Required | Description                                                                                                                                         |
| --------------- | -------- | -------------------- | -------- | --------------------------------------------------------------------------------------------------------------------------------------------------- |
| MODEL           | `string` | create/update/delete | Yes      | Model class path.                                                                                                                                   |
| TABLE           | `string` | list                 | Yes      | Set the main table name.                                                                                                                            |
| TABLE_ALIAS     | `string` | list                 | No       | Set the table alias name to use it in the entire query instead of the table name, this is useful if you're going to join many tables in your query. |
| USE_SOFT_DELETE | `bool`   | list                 | No       | Determine if repository will use soft deletes to check deleted records in the list method.                                                          |
| DATA | `array`   | create/update/delete                 | No       | Get data from the request and added it directly to the model. New In `1.2`.                                                          |

# Macroable

Starting from version `1.2`, The repository manager now is `Macroable`.

> Be aware that the repository manager is calling query build methods dynamically in the `list` method, so when you create a macro method be careful with your macro name as the manager will check first in the builder before accessing it to the `Macroable` trait.