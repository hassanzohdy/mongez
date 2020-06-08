Creating a model file into target module

``` 
php artisan engez:model <model-name> <module-name>
```

Replace ```<model-name>``` by your desired name. 

```
By default when you create new model automatically will generate migration file with entered commands values and if your database driver is mongo will generate also a schema file 
```

## Available command options
| Options        | Usage        | Default      | Description      |   
| -------------  |:------------ | :----------- | :-----------     |
| --data         | Set all string columns separated by `,` (comma) |
| --parent       | Parent module|              |                   |
| --table        |  | `Lower case of module name` | |
| --uploads      | Set all uploads columns separated by `,` (comma)| |
| --index        | Set all index columns separated by `,` (comma)| |
| --unique       | Set all unique columns separated by `,` (comma)| |
| --int          | Set all integer columns separated by `,` (comma)| |
| --double       | Set all double columns separated by `,` (comma)| |
| --bool         | Set all bool columns separated by `,` (comma)| ||
