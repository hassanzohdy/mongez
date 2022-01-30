<?php
namespace HZ\Illuminate\Mongez\Macros\Console;

class Router
{
    /**
     * Route an API resource to a controller. (extended)
     * 
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\PendingResourceRegistration
     */
    public function restfulApi($name, $controller, array $options = [])
    {
        return function ($name, $controller, array $options = []) {

            $this->apiResource($name, $controller, $options = []);
            
            if (config('mongez.admin.patchable', false)) {
                $this->patch($name, 'patch');
            }
        };
    }

}