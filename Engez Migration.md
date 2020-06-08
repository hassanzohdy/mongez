Creating a migration file into target module

``` 
php artisan engez:migration <migration-name> <module-name>
```

Replace ```<migration-name>``` by your desired name. 

Replace ```<module-name>``` by your desired name. 


## Available command options
| Options        | Usage        | Default      | Description      |   
| -------------  |:------------ | :----------- | :-----------     |
| --data         | Set all string columns separated by `,` (comma) |
| --create       |              |     `true`, `false`           |     [create option](#CreateOption)             |
| --parent       |     |              |                   |
| --table        |  | `Lower case of module name` | |
| --uploads      | Set all uploads columns separated by `,` (comma)| |
| --index        | Set all index columns separated by `,` (comma)| |
| --unique       | Set all unique columns separated by `,` (comma)| |
| --int          | Set all integer columns separated by `,` (comma)| |
| --double       | Set all double columns separated by `,` (comma)| |
| --bool         | Set all bool columns separated by `,` (comma)| ||

### Additional Notes

<a name = "CreateOption"></a>
```--create option``` 
To take the control of the migration file is it use for creating new table or modify current table
