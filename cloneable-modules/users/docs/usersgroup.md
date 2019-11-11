# UsersGroup API DOCS

# Base URL
http://localhost

# Other resources 

 
# Headers

Authorization: key your token

Accept : application/json

# API 

| Route                        | Request Method | Parameters | Response  |
| -----------                  | -----------    |----------- |---------- |
| /api/admin/usersgroups            | POST           |  [Create Parmaters](#Create)|[Response](#Response)|
| /api/admin/usersgroups | GET           |-|  [Response](#Response)         |
|/api/admin/usersgroups/{id}         | GET           |  - |  [Response](#Response)         |
|/api/admin/usersgroups/{id}        |PUT           |  [Update Parmaters](#Update)|[Response](#Response)     |
|/api/admin/usersgroups/{id}        |DELETE           |  -|[Response](#Response)| 
|/api/usersgroups        |GET           |-| [Response](#Response)|
|/api/usersgroups/{id}        |GET           |-|[Response](#Response)|


# <a name="Create"> </a> Create new UsersGroup 

```json
{
"name" : "text",
"permissions" : "text",
} 
```

# <a name="Update"> </a> Update UsersGroup

```json
{
"name" : "text",
"permissions" : "text",
} 
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
