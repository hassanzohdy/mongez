# Macro Blueprint

This macro is used on migrations and creating schema tables in general.

This macro class extends the `Illuminate\Database\Schema\Blueprint` class.

- [Macro Blueprint](#macro-blueprint)
- [Adding the macro](#adding-the-macro)
- [Available methods](#available-methods)
  - [Loggers](#loggers)
    - [Method Syntax](#method-syntax)
    - [Example](#example)


# Adding the macro
By default, the macro is enabled once you installed the package successfully.

To enable/disable it go to `app/mongez.php` and add or remove the macro in the `macros` section.

```php

    'macros' => [
        Illuminate\Database\Schema\Blueprint::class => 
        HZ\Illuminate\Mongez\Macros\Database\Schema\Blueprint::class,
    ],
```

# Available methods
- [loggers](#loggers)
  
## Loggers
Well, this is a good method to call if you want to trace every action happens in the table by the user as it adds the following columns:

```
created_at timestamp
created_by int 
updated_at timestamp
updated_by int 
deleted_at timestamp nullable INDEX
deleted_by 
```

### Method Syntax
`$table->loggers(string $createdBy = 'created_by', string $updatedBy = 'updated_by', string $deletedBy = 'deleted_by'): void`

### Example

database/migrations/create-user.php
```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');

            // the loggers() method will add the following columns:
            // created_at timestamp
            // created_by int
            // updated_at timestamp
            // updated_by int
            // deleted_at timestamp
            // deleted_by int
            // and index for *deleted_at
            $table->loggers();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

```

Of course you can change the *_by columns based on your needs so you can change the default passed arguments to the method.