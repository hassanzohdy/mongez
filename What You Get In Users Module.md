# What you get when you clone Users Module
The cloned users module will be with the same module structure.
This module only work in admin site

We provide the whole experience of dashboard users within the permissions of every dashboard user.

``Note`` You do not need to make ``engez:migrate`` after clone the users module we automatically do it for you.

## What's auto migrated tables of users module 
- users 
- users group
- permission
- user tokens

## What is the purpose of user tokens. 
user tokens is the identifier for every user and this token changed with every user login.

``Note`` this token must be send in request headers in ``Authorization`` key with all admin routes.

## How mongez package handle the user permissions.

Every user have a user group and every group has a list of permission and we have ``Check permission`` middleware that check that is the admin has the authority to entered this route or not. 

## What if won't to have permissions middleware 
You can remove the ``checkPermission`` middleware from middleware array of admin routes of any module


``Note`` you can get the the ``CheckPermission`` middleware in ``app\Modules\Users\Middleware``

``Note`` the ``CheckPermission`` middleware works only on admin routes of modules.

``Note`` in case you clone users and in next generated modules will insert the admin routes of it in permissions table automatically and ``CheckPermission`` middleware will be added in admin middleware list.

## If any thing special in cloned module 

Every thing we talk about the generated module will be available in cloned module without any different.