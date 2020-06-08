# Create new module

Creating a module is simple and easy. Run the following command to create a module.

``` 
php artisan engez:module <module-name>
```

Replace ```<module-name>``` by your desired name.

The new module will be placed in your ```app/modules``` directory.

## Available command options
| Options        | Usage        | Default      | Description      |   
| -------------  |:------------ | :----------- | :-----------     |
| --data         | Set all string columns separated by `,` (comma) |
| --parent       | Parent module|              |                   |
| --controller   | Controller name| `Studly case of module name` | 
| --type         | `all`, `admin`, `site`  | `all`  | [Type option](#typeOption)
| --model        |  | `Singular and studly of module name` | | |
| --table        |  | `Lower case of module name` ||
| --resource     |  | `Singular and studly of module name` | |
| --repository   |  | `Studly of module name ` | |
| --path         |  | |
| --uploads      | Set all uploads columns separated by `,` (comma)| |
| --index        | Set all index columns separated by `,` (comma)| |
| --unique       | Set all unique columns separated by `,` (comma)| |
| --int          | Set all integer columns separated by `,` (comma)| |
| --double       | Set all double columns separated by `,` (comma)| |
| --bool         | Set all bool columns separated by `,` (comma)| ||




## Additional Notes

<a src = "typeOption"></a>

`--type option` have all,site and admin options
by ``default`` will be `all`  
depend on your choice that effect on routes and controllers directories

if your choice ``all`` will be two routes files in routes admin.php, site.php 
in controllers will be Admin/module-name</strong>Controller.php and site/module-name</strong>Controller.php

if your choice ``admin`` or ``site`` will generate only your choice in routes and controllers directories.


[What you get in generated module](./WhatYouGetInModules)
