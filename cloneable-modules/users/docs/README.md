# User API DOCS

# Base URL
http://localhost

# Headers

Authorization: key your token

Accept : application/json

# API 

| Route                        | Request Method | Parameters | Response  |
| -----------                  | -----------    |----------- |---------- |
| /api/admin/users            | POST           |  [Create Parmaters](#Create)|[Response](#Response)|
| /api/admin/users | GET           |-|  [Response](#Response)         |
|/api/admin/users/{id}         | GET           |  - |  [Response](#Response)         |
|/api/admin/users/{id}        |PUT           |  [Update Parmaters](#Update)|[Response](#Response)     |
|/api/admin/users/{id}        |DELETE           |  -|[Response](#Response)| 

# <a name="Create"> </a> Create new User 

```json
{
"name":"",
"email":"",
"password":""
} 
```

# <a name="Update"> </a> Update User

```json
{
"name":"string",
"email":"string",
"password":"string"
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