# moduleName API DOCS

# Base URL
BaseUrl

# Other resources 

 
# Headers

Authorization: key your token

Accept : application/json

# API 

| Route                        | Request Method | Parameters | Response  |
| -----------                  | -----------    |----------- |---------- |
| /api/admin/routeName            | POST           |  [Create Parmaters](#Create)|[Response](#Response)|
| /api/admin/routeName | GET           |-|  [Response](#Response)         |
|/api/admin/routeName/{id}         | GET           |  - |  [Response](#Response)         |
|/api/admin/routeName/{id}        |PUT           |  [Update Parmaters](#Update)|[Response](#Response)     |
|/api/admin/routeName/{id}        |DELETE           |  -|[Response](#Response)| 
|/api/routeName        |GET           |-| [Response](#Response)|
|/api/routeName/{id}        |GET           |-|[Response](#Response)|


# <a name="Create"> </a> Create new moduleName 

```json
data 
```

# <a name="Update"> </a> Update moduleName

```json
data 
```
# <a name="Response"> </a> Responses 

## Unauthorized error

__*Response code : 401*__
```json 
{
    "message" : "Unauthenticated"
}
```

## Validation error 
__*Response code : 422*__

```json 
{
    "errors" {
        "Key" : "Error message"
    }
}
```
## Success  
__*Response code : 200*__
```json 
{
    "records" [
        {

        },
    ]
}
```
