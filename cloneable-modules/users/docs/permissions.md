# Permission API DOCS

# Base URL
http://localhost

# Other resources 

 
# Headers

Authorization: key your token

Accept : application/json

# API 

| Route                        | Request Method | Parameters | Response  |
| -----------                  | -----------    |----------- |---------- |
| /api/admin/permissions            | POST           |  [Create Parmaters](#Create)|[Response](#Response)|
| /api/admin/permissions | GET           |-|  [Response](#Response)         |
|/api/admin/permissions/{id}         | GET           |  - |  [Response](#Response)         |
|/api/admin/permissions/{id}        |PUT           |  [Update Parmaters](#Update)|[Response](#Response)     |
|/api/admin/permissions/{id}        |DELETE           |  -|[Response](#Response)| 
|/api/permissions        |GET           |-| [Response](#Response)|
|/api/permissions/{id}        |GET           |-|[Response](#Response)|


# <a name="Create"> </a> Create new Permission 

```json
{
"name" : "text",
"permission" : "text",
"key" : "text",
"group" : "text",
} 
```

# <a name="Update"> </a> Update Permission

```json
{
"name" : "text",
"permission" : "text",
"key" : "text",
"group" : "text",
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
