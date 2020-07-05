# Setting API DOCS

# Base URL
http://localhost

# Other resources 

 
# Headers

Authorization: key your token

Accept : application/json

# API 

| Route                        | Request Method | Parameters | Response  |
| -----------                  | -----------    |----------- |---------- |
| /api/admin/settings            | POST           |  [Create Parmaters](#Create)|[Response](#Response)|
| /api/admin/settings | GET           |-|  [Response](#Response)         |
|/api/admin/settings/{id}         | GET           |  - |  [Response](#Response)         |
|/api/admin/settings/{id}        |PUT           |  [Update Parmaters](#Update)|[Response](#Response)     |
|/api/admin/settings/{id}        |DELETE           |  -|[Response](#Response)| 
|/api/settings        |GET           |-| [Response](#Response)|
|/api/settings/{id}        |GET           |-|[Response](#Response)|


# <a name="Create"> </a> Create new Setting 

```json
{
"name" : "String"
"group" : "String"
"type" : "String"
"value" : "String"
} 
```

# <a name="Update"> </a> Update Setting

```json
{
"name" : "String"
"group" : "String"
"type" : "String"
"value" : "String"
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
