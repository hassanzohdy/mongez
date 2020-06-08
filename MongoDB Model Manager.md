# MongoDB Model Manager

The `ModelManager` basically extends the `Eloquent` model which makes won't make any conflict with your current code.

This model works with `MongoDB` driver.

When you create a module using the `Module creator command line`[./modules], the package will detect the database driver and assign to the model the proper base model class either for `MYSQL` or `MongoDB`. 

Pretty much everything in the [Mysql Model Manger](./model-manager) are implemented here, but there are more methods to talk about in our MongoDB Manager.

The model base class is `HZ\Illuminate\Mongez\Managers\Database\MongoDB\Model`

# Auto Incremented IDs

As mongodb creates an [object id](https://docs.mongodb.com/manual/reference/method/ObjectId/), and its column will be `_id`, but that column has a generated 24 character length value, you won't probably want to share that id with others.

For example you want to create a route like this `/users/12` not `/users/507f1f77bcf86cd799439011` so the [MongoDB Model Manager](./mongodb-model-manager) handles this for you.

Once you create a new record by the model, the `id` column will be added and auto incremented as if it is in MYSQL database.

For example

```php
$user = new App\Modules\Users\Models\User;

$user->name = 'Hasan Zohdy';

$user->email = 'hassanzohdy@gmail.com';

$user->save();

dd($user->info()); // [id => 1, name => 'Hasan Zohdy', email => 'hassanzohdy@gmail.com']

// OR

echo $user->id; // 1

// create another record

$user = new App\Modules\Users\Models\User;

$user->name = 'John Doe';

$user->email = 'john-doe@sitename.com';

$user->save();

echo $user->id; // 2
```

So you can now normally use the same `id` column with auto incremental values.

# Get next id

Return the next id of a model.

```php
$nextId = User::getNextId(); // 1
$nextId = User::getNextId(); // 1
$nextId = User::getNextId(); // 1
```

This feature is already exists in [MYSQL macro](./macro-query) and is implemented in MongoDB Model Manager as well.


# Next id

Update next id of the model and return the new id

```php
$nextId = User::nextId(); // 1
$nextId = User::nextId(); // 2
$nextId = User::nextId(); // 3
```

# Last Insert id

Get last inserted id of a model

```php
$nextId = User::lastInsertId(); // 1
```

# info

Get all document info 

```php
$user = User::find(1);

dd($user->info()); // [all document info]
```

# sharedInfo

Sometimes you want to inject a document from a model|`collection` to another, in that case you won't need to inject all the document.

For example if we've the following schema of `users` collection

```json
{
    "_id": "objectId(...)",
    "id": 1,
    "name": {
        "first": "Hasan",
        "last": "Zohdy",
    },
    "email": "hassanzohdy@gmail.com",
    "password": "$2$10$pQWRFPVDZPT@#$RDEC",
    "image": "path-to-image",
    "totalPosts": 67
}
```

When you create a post, we need to define the `author` of the post,

So here is the schema for a `posts` collection

```json
{
    "_id": "objectId(...)",
    "id": 1,
    "title": "Hello World",
    "description": "An amazing world? I doubt!",
    "image": "path-to-post-image",
    "likes": 0,
    "author": {
        "_id": "objectId(...)",
        "id": 1,
        "name": {
            "first": "Hasan",
            "last": "Zohdy",
        },
        "email": "hassanzohdy@gmail.com",
        "password": "$2$10$pQWRFPVDZPT@#$RDEC",
        "image": "path-to-image",
        "totalPosts": 67
    }
}
```

As we can see, we injected the whole `user` info which is not a good idea as we don't need to store the `password` nor the `_id` or even `totalPosts` in any other collections, this will make the document size larger for no need.

So we can define what data could be shared with other documents when we inject it.

```php
<?php

namespace App\Modules\Users\Models;

use HZ\Illuminate\Mongez\Managers\Database\MongoDB\Model;

class User extends Model
{
    /**
     * {@inheritDoc}
     */
    public function sharedInfo(): array 
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image,
        ];
    }
}
```

Now when we create a new post:

```php
$postModel = new Post;

$user = User::find(1);

$postModel->title = 'Hello World'; 
$postModel->description = 'An amazing world? I doubt!'; 
$postModel->image = 'path-to-post-image'; 
$postModel->likes = 0; 
$postModel->author = $user->sharedInfo(); 

$postModel->save(); 
```

This approach will be a much better than storing the entire user document in the post model, as we only need the user `id`, `name` and `image` only.    


# The association methodology
Let's assume we've a `Team` model which contains list of members `User` Model, So the schema would be something like:
`teams.json`

```json
{
    "_id": "objectId(...)",
    "id": 12,
    "name": "My beloved team",
    "members": [
        {
            "id": 12,
            "name": "Mr. Hendawy EL Mahelawy",
        },
        {
            "id": 51,
            "name": "Mr Mangawy El Mahelawy",
        },
        {
            "id": 15,
            "name": "Mr Bendawy El Mahelawy",
        },
    ]
}
```

So the team sometimes may get bigger and new members could be added:

```php
$MissHendElMahelawy = User::find(81);

$ElMahelawyTeam = Team::find(185);

$members = $ElMahelawyTeam->members;

$members[] = $missHendElMahelawy->sharedInfo();

$ElMahelawyTeam->members = $members;

$ElMahelawyTeam->save();
```

The previous code will inject the new team member to the team members list.

Instead of doing that, use the [`associate method instead`](#associate-method).

# Associate method

Associate the given model/data to the given column in the current model.

`associate(array|Model $info, string $column): Model`

In our previous example, we can change it for a better code:

```php
$MissHendElMahelawy = User::find(81);

$ElMahelawyTeam = Team::find(185);

$ElMahelawyTeam->associate($MissHendElMahelawy, 'members')->save();
```

This will do exactly the same in the database.

# Disassociate

What if we want to remove `Mr Hendawy` from the team members list?

We could use the `disassociate` method for that purpose.
`disassociate(array|Model $info, string $column): Model`


```php
$mrHendawy = User::find(12);

$ElMahelawyTeam = Team::find(185);

$ElMahelawyTeam->disassociate($mrHendawy, 'members')->save();
```

Now `Mr Hendawy` no longer part of the team, and the `members` array is reindexed.


# Reassociate

Sometimes the team member data is updated, so we want to update it in the `members` list as well.

`reassociate(array|Model $info, string $column): Model`


```php
// let's say we want to update Mr. Mangawy's name
$mrMangawy = User::find(51);

$mrMangawy->name = 'The Amazing Mr Mangawy';

$mrMangawy->save();

// now we need to modify his membership info in the team.

$ElMahelawyTeam = Team::find(185);

$ElMahelawyTeam->reassociate($mrMangawy, 'members')->save();
```

> Please note that Mongez doesn't conflict with [Laravel MongoDB Driver Relationships](https://github.com/jenssegers/laravel-mongodb#relations), but it implements a different approach.



