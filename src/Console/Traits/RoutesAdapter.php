<?php

namespace HZ\Illuminate\Mongez\Console\Traits;

use HZ\Illuminate\Mongez\Console\Stub;

trait RoutesAdapter
{
    use EngezStub, EngezTrait;

    /**
     * Routes directory inside module folder
     * 
     * @var string
     */
    protected string $routesDirectory;

    /**
     * Generate routes files
     * 
     * @return void
     */
    protected function createRoutes()
    {
        $this->routesDirectory = $this->modulePath("routes");

        if ($this->optionHasValue('parent')) {
            return $this->createRoutesFromParent();
        }

        if ($this->isAdminController()) {
            $this->createAdminRoutesFile();
        }

        if ($this->isSiteController()) {
            $this->createSiteRoutesFile();
        }
    }

    /**
     * Create routes in an existing parent routes files
     * 
     * @return void
     */
    protected function createRoutesFromParent()
    {
        $controller = $this->studly($this->argument('controller'));

        if ($this->isAdminController()) {
            $stub = new Stub($filePath = $this->modulePath('routes/admin.php'));
            $route = $this->kebab($this->optionHasValue('route') ? $this->option('route') : $this->getModule());

            $routeStatement = "Route::{{ methodName }}('{{ route-path }}', {{ ControllerClass }}::class);";

            $replacements = [
                '{{ ControllerClass }}' => $this->controllerName,
                '{{ route-path }}' => $route,
            ];

            if (\config('mongez.admin.patchable', false)) {
                $replacements['{{ methodName }}'] = 'restfulApi';
            } else {
                $replacements['{{ methodName }}'] = 'apiResource';
            }

            $stub->appendAfter('use Illuminate\Support\Facades\Route;', "use App\\Modules\\{$this->getModule()}\\Controllers\\Admin\\{$this->controllerName};");
            $stub->appendAfter('    // Sub API routes DO NOT remove this line', '    // ' . $controller . PHP_EOL .  '    ' . str_replace(array_keys($replacements), array_values($replacements), $routeStatement) . PHP_EOL);

            $stub->saveTo($filePath);
        }


        if ($this->isSiteController()) {
            $stub = new Stub($filePath = $this->modulePath('routes/site.php'));
            $route = $this->kebab($this->optionHasValue('route') ? $this->option('route') : $this->getModule());

            $routeStatement = "Route::get('{{ route-path }}', [{{ ControllerClass }}::class, '{{ methodName }}']);";

            $replacements = [
                '{{ ControllerClass }}' => $this->controllerName,
                '{{ route-path }}' => $route,
                '{{ methodName }}' => 'index',
            ];

            $stub->appendAfter('    // Sub API routes DO NOT remove this line', '    ' . str_replace(array_keys($replacements), array_values($replacements), $routeStatement) . PHP_EOL);

            $replacements['{{ route-path }}'] .= '/{id}';
            $replacements['{{ methodName }}'] = 'show';
            $stub->appendAfter('    // Sub API routes DO NOT remove this line', '    // ' . $controller . PHP_EOL . '    ' . str_replace(array_keys($replacements), array_values($replacements), $routeStatement));

            $stub->appendAfter('use Illuminate\Support\Facades\Route;', "use App\\Modules\\{$this->getModule()}\\Controllers\\Site\\{$this->controllerName};");

            $stub->saveTo($filePath);
        }
    }

    /**
     * Create admin routes file
     * 
     * @return void
     */
    protected function createAdminRoutesFile()
    {
        $route = $this->kebab($this->optionHasValue('route') ? $this->option('route') : $this->getModule());

        $replacements = [
            '{{ ControllerClass }}' => $this->controllerName,
            '{{ route-path }}' => $route,
            '{{ moduleName }}' => $this->getModule(),
            '{{ methodName }}' => 'apiResource',
        ];

        if (\config('mongez.admin.patchable', false)) {
            $replacements['{{ methodName }}'] = 'restfulApi';
        } else {
            $replacements['{{ methodName }}'] = 'apiResource';
        }

        $authIsEnabled = $this->optionHasValue('auth') ? $this->option('auth') : $this->config('router.auth.enabled', true);

        if ($authIsEnabled) {
            $authMiddlewareName = $this->config('router.auth.middleware', 'authorized');

            $replacements['{{ authMiddleware }}'] = $this->tabWith(
                "'middleware' => ['$authMiddlewareName'],"
            );
        }

        $stub = $this->stubInstance('routes/admin');

        $stub->replace($replacements);

        if ($authIsEnabled === false) {
            $stub->removeLine('{{ authMiddleware }}');
        }

        $stub->appendAfter('use Illuminate\Support\Facades\Route;', "use App\\Modules\\{$this->getModule()}\\Controllers\\Admin\\{$this->controllerName};");

        // create the route file
        $filePath = $this->routesDirectory . '/admin.php';

        $stub->saveTo($filePath);
    }

    /**
     * Create site routes file
     * 
     * @return void
     */
    protected function createSiteRoutesFile()
    {
        $route = $this->kebab($this->optionHasValue('route') ? $this->option('route') : $this->getModule());

        $replacements = [
            '{{ ControllerClass }}' => $this->controllerName,
            '{{ route-path }}' => $route,
            '{{ moduleName }}' => $this->getModule(),
        ];

        $stub = $this->stubInstance('routes/site');

        $stub->replace($replacements);

        $stub->appendAfter('use Illuminate\Support\Facades\Route;', "use App\\Modules\\{$this->getModule()}\\Controllers\\Site\\{$this->controllerName};");

        // create the route file
        $filePath = $this->routesDirectory . '/site.php';

        $stub->saveTo($filePath);
    }

    /**
     * update parent routes
     * 
     * @return void
     */
    protected function updateRoutes()
    {
        $type = $this->option('type');

        $controller = $this->info['controller'];

        $this->controllerName = basename(str_replace('\\', '/', $controller));

        // replace module name
        $routeModule  =  strtolower($this->info['moduleName']);
        if (in_array($type, ['all', 'site'])) {

            // generate the site routes file

            $content = $this->files->get($this->modulePath("routes/site.php"));
            $content = str_replace(
                '// Sub API CRUD routes',
                "// Sub API CRUD routes
    Route::get('/{$this->info['parent']}/{$routeModule}/{id}','{$this->controllerName}Controller@index');
    Route::get('/{$this->info['parent']}/$routeModule}/{id}','{$this->controllerName}Controller@show');",
                $content
            );
            $this->files->put($this->modulePath("routes/site.php"), $content);
        }

        if (in_array($type, ['all', 'admin'])) {
            $content = $this->files->get($this->modulePath("routes/site.php"));
            $content = str_replace(
                '// Sub API CRUD routes',
                "// Sub API CRUD routes
    Route::get('/{$this->info['parent']}/{$routeModule}','{$this->controllerName}Controller@index');
    Route::get('/{$this->info['parent']}/{$routeModule}/{id}','{$this->controllerName}Controller@show');",
                $content
            );

            $this->files->put($this->modulePath("routes/site.php"), $content);
            $content = $this->files->get($this->modulePath("routes/admin.php"));
            $content = str_replace(
                '// Sub API CRUD routes',
                "// Sub API CRUD routes
    Route::apiResource('/{$this->info['parent']}/{$routeModule}', '{$this->controllerName}Controller');",
                $content
            );
            $this->files->put($this->modulePath("routes/admin.php"), $content);
        }
        return;
    }
}
