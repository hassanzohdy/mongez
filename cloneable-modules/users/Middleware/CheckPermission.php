<?php

namespace App\Modules\Users\Middleware;

use Str;
use Closure;
use Illuminate\Support\Facades\Route;
use App\Modules\Users\Models\UserGroup;
use App\Modules\Users\Models\Permission;


class CheckPermission
{

    /**
     * Routes that expect from permissions.
     *
     * @var array
     */
    protected $expectRoutes = [
        '/api/admin/login',
        '/api/admin/logout',
        '/api/admin/users/permissions'
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // For getting the absolute path of request.
        $routeFormat = $request->server('PATH_INFO');

        $requestMethod = $request->server('REQUEST_METHOD');

        if (in_array($routeFormat, $this->expectRoutes)) return $next($request);

        $replacementString = array_filter(explode('/', $routeFormat));
        $routeName = $replacementString[sizeof($replacementString)];
        
        if (is_numeric($routeName)) {
            $routeName = $replacementString[sizeof($replacementString)-1];
        }

        $replacementString = "{".Str::singular($routeName)."}";

        // replace any numeric value with {routeName}
        $routeFormat = preg_replace('/([\d])+/', "{$replacementString}", preg_quote($routeFormat));

        $routeCollections = Route::getRoutes();
        foreach ($routeCollections as $route) {
            if ($routeFormat == '/'.$route->uri && $requestMethod == $route->methods[0]){
                $routeKey = $route->action['as'];
            }
        }

        $userGroupId = request()->user()->user_group_id;

        $userGroup = UserGroup::find($userGroupId);
        $userGroupPermissions = explode(',', $userGroup->permissions);

        $currentRequestPermission = Permission::where('key', $routeKey)->first();
        
        if (! $currentRequestPermission){
            return response()->json('Please Add route to permissions table');
        }
        
        if (in_array($currentRequestPermission->id, $userGroupPermissions)) {
            return $next($request);
        } else {
            return response()->json(['Access Denied'], 403);
        }
    }
}