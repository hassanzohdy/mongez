# Create a repository file into target module

``` 
php artisan engez:repository <repository-name> <module-name>
```

Replace ```<repository-name>``` by your desired name. 

Replace ```<module-name>``` by your desired name. 

## Available command options
| Options        | Usage        | Default      | Description      |   
| -------------  |:------------ | :----------- | :-----------     |
| --data         | Set all string columns separated by `,` (comma) |
| --parent       | Parent module|              |                   |
| --model        |  | `Singular and studly of module name` | | |
| --uploads      | Set all uploads columns separated by `,` (comma)| |
| --int          | Set all integer columns separated by `,` (comma)| |
| --double       | Set all double columns separated by `,` (comma)| |
| --bool         | Set all bool columns separated by `,` (comma)| ||
| --resource     |  | `Singular and studly of module name` ||

