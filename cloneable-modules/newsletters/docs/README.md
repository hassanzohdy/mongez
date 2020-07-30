# NewsLetter API DOCS

# Base URL
http://localhost

# Other resources
[ContactU](./contactu.md) 

 
# Headers

Authorization: key your token

Accept : application/json

# API 

| Route                        | Request Method | Parameters | Response  |
| -----------                  | -----------    |----------- |---------- |
| /api/admin/newsletters            | POST           |  [Create Parmaters](#Create)|[Response](#Response)|
| /api/admin/newsletters | GET           |-|  [Response](#Response)         |
|/api/admin/newsletters/{id}         | GET           |  - |  [Response](#Response)         |
|/api/admin/newsletters/{id}        |PUT           |  [Update Parmaters](#Update)|[Response](#Response)     |
|/api/admin/newsletters/{id}        |DELETE           |  -|[Response](#Response)| 
|/api/newsletters        |GET           |-| [Response](#Response)|
|/api/newsletters/{id}        |GET           |-|[Response](#Response)|


# <a name="Create"> </a> Create new NewsLetter 

```json
{
} 
```

# <a name="Update"> </a> Update NewsLetter

```json
{
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
