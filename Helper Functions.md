# Helper Functions

Sometimes you want to do some quick function for easy access, for example in the [Helper functions](./helper-functions) file we have the [user()](./helper-functions#user) function which returns the object of the current user.

Best way to inject your `functions` files is to add it in your `composer.json` file in the `autoload` section, like this:

```json
"autoload": {
    "files": [
        "app/Helpers/my-functions.php"
    ]
}
```

> Don't forget to run `composer dump-autoload` after adding the helper functions.

# Available functions
- [Helper Functions](#helper-functions)
- [Available functions](#available-functions)
- [User](#user)
  - [Example](#example)
- [Repo](#repo)
  - [Example](#example-1)
- [Pre](#pre)
  - [Example](#example-2)
- [Pred](#pred)
- [str_remove_first](#strremovefirst)
  - [Example](#example-3)
- [array_remove](#arrayremove)
  - [Example](#example-4)

# User

`user(): \Illuminate\Database\Eloquent\Model`

This is a shorthand function to get the object of current user

## Example

```php

echo user()->id; // 12
```

> The `user()` function will return `null` if three is no authorized user model.

# Repo

`repo(string $repository): HZ\Illuminate\Mongez\Contracts\Repositories\RepositoryInterface`

This is the very fast and simple way to get a [Repository object](./repositories) 

## Example

```php

$usersRepository = repo('users');

$userDetails = $usersRepository->get(15);
```

> If the passed repository name to the function does not exist in the `repositories` array, a `NotFoundRepositoryException` will be thrown. 

# Pre

`pre(mixed $var): void`

I do love this function more than the [var_dump()](http://php.net/manual/en/function.var-dump.php) which simply prints out the given variable using the [print_r()](http://php.net/manual/en/function.print-r.php) in a `pre` tag.


## Example

```php

$my_array = [12, 42, 51];

pre($my_array); 

// output
/*
Array
(
    [0] => 12
    [1] => 42
    [2] => 51
)
*/
```

# Pred

`pred(mixed $var): void`

Exactly the same as but after it prints out the given variable, the application script will stop execution using `die()` method after calling [pre()](#pre) method.


# str_remove_first

`str_remove_first(string $needle, string $string): string`

Remove from the given string the first occurrence for the given needle

## Example

```php

$name = 'Hello, World!';

echo str_remove_first('Hello', $name); // ,World!

```

> This is an alias function to [Str::removeFirst()](./macro-str#removefirst) method.


# array_remove

`array_remove($value, array $array, bool $removeFirstOnly = false): array`

Remove value from the given array

if the `$removeFirstOnly` argument is set to true, it will remove the first matching value only, otherwise `default` it will remove all values from the array.

## Example

```php

$numbers = [1, 2, 3, 4, 6, 1, 2];

$numbers = array_remove(1, $numbers);

dd($numbers); // [2, 3, 4, 6, 2]

// remove the first matching value only
$numbers = array_remove(2, $numbers, true);

dd($numbers); // [3, 4, 6, 2]

```

> This is an alias function to [Arr::remove()](./macro-arr#remove) method.
