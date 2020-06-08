# Helpers

Helpers are groups of `classes` and helper `functions`.

Mainly, helpers are used to do some functions for other classes/functions.

- [Helpers](#helpers)
- [Helper classes](#helper-classes)
- [Helper Functions](#helper-functions)

# Helper classes

For example, the [Select class](./select-helper) is used to manage any passed selections to any [Repository](#repositories).

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

Once you install the package, the [Helper functions file](./helper-functions) is autoloaded to the project.
