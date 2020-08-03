# ContactU API DOCS

# Base URL
http://localhost

# Other resources 

 
# Headers

Authorization: key your token

Accept : application/json

# API 

| Route                        | Request Method | Parameters | Response  |
| -----------                  | -----------    |----------- |---------- |
| /api/admin/contactus            | POST           |  [Create Parmaters](#Create)|[Response](#Response)|
| /api/admin/contactus | GET           |-|  [Response](#Response)         |
|/api/admin/contactus/{id}         | GET           |  - |  [Response](#Response)         |
|/api/admin/contactus/{id}        |PUT           |  [Update Parmaters](#Update)|[Response](#Response)     |
|/api/admin/contactus/{id}        |DELETE           |  -|[Response](#Response)| 
|/api/contactus        |GET           |-| [Response](#Response)|
|/api/contactus/{id}        |GET           |-|[Response](#Response)|


# <a name="Create"> </a> Create new ContactU 

```json
{
"name" : "String"
"email" : "String"
"phone_number" : "String"
"subject" : "String"
"message" : "String"
} 
```

# <a name="Update"> </a> Update ContactU

```json
{
"name" : "String"
"email" : "String"
"phone_number" : "String"
"subject" : "String"
"message" : "String"
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
