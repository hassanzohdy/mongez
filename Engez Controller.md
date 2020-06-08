# Create a controller file into target module

``` 
php artisan engez:controller <controller-name> <module-name>
```

Replace ```<controller-name>``` by your desired name. 

Replace ```<module-name>``` by your target module name. 


## Available command options
| Options        | Usage        | Default      | Description      |   
| -------------  |:------------ | :----------- | :-----------     |
| --parent       |     |              |                   |
| --type         | `all`, `admin`, `site`  | `all`  | [Type option](#typeOption)
| --repository   |  |  |[Repository option](#repoOption)|

### Additional Notes

<a name = "repoOption"></a>
```--repository option``` 
Set the repository name that controller deal with
