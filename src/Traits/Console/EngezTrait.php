<?php
namespace HZ\Illuminate\Mongez\Traits\Console;

use File;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use HZ\Illuminate\Mongez\Helpers\Mongez;

trait EngezTrait
{
    /**
     * Create the file
     * 
     * @param  string $filePath
     * @param  string $content
     * @param  string $fileType
     * @return void
     */
    protected function createFile($filePath, $content, $fileType)
    {
        $filePath = str_replace('\\', '/', $filePath);
        
        $createFile = true;
        if (File::exists($filePath)) {
            $createFile = false;
            $createFile = $this->confirm($fileType . ' exists, override it?');
        }

        if ($createFile) {
            File::put($filePath, $content);
        }
    }
    
    /**
     * Get relative path to base path
     * 
     * @param  string $path
     * @return string 
     */
    protected function path($path)
    {
        return Mongez::packagePath('/module/' . $path);
    }

    /**
     * Get the final path of the module for the given relative path
     * 
     * @param   string $relativePath
     * @return  string 
     */
    protected function modulePath(string $relativePath): string
    {
        $targetModule = $this->info['moduleName'];
        if (isset($this->info['parent'])) {
            $targetModule = $this->info['parent'];
        }
        return base_path("app/Modules/{$targetModule}/$relativePath");
    }

    /**
     * Check if the given directory path is not created, if so then create one
     * 
     * @param  string $directoryPath
     * @return  void
     */
    public function checkDirectory(string $directoryPath)
    {
        $directoryPath = str_replace('\\', '/', $directoryPath);
        if (!File::isDirectory($directoryPath)) {
            File::makeDirectory($directoryPath, 0777, true);
        }
    }

    /**
     * Get proper repository name
     * 
     * @param  string $repositoryName
     * @return string
     */
    private function adjustRepositoryName(string $repositoryName): string
    {
        return Str::plural(
            Str::camel(basename(str_replace('\\', '/', $repositoryName)))
        );
    }

    /**
     * Output Missing Required options to console
     * 
     * @param  string $message
     * @return void 
     */
    public function missingRequiredOption($message)
    {
        Command::error($message);
        die();
    } 

    /**
     * Get a repository shortcut name based on the given module name
     * 
     * @param  string $module
     * @return string 
     */
    public function repositoryShortcutName(string $module): string
    {
        return Str::plural(Str::camel($module));
    }

    /**
     * Set module name to config file.
     * 
     * @return void
     */
    protected function addModule()
    {
        Mongez::append('modules', strtolower($this->moduleName));
    }

    /**
     * Update module name to config file.
     * 
     * @return void
     */
    protected function markModuleAsInstalled()
    {
        Mongez::updateStorageFile();
    }
    
    /**
     * Create migration file of table in mysql 
     *
     * @param string $dataFileName
     * @return void 
     */
    protected function createMigration()
    {
        $migrationsOptions = [
            'migrationName' => 'create_' .Str::plural(strtolower($this->info['modelName'])).'_table',
            'module' => $this->info['moduleName'],
        ];

        if ($this->optionHasValue('index')) {
            $migrationsOptions['--index'] = $this->option('index');
        }

        if ($this->optionHasValue('unique')) {
            $migrationsOptions['--unique'] = $this->option('unique');
        }
        
        if ($this->optionHasValue('data')) {
            $migrationsOptions['--data'] = $this->option('data');
        }

        if ($this->optionHasValue('uploads')) {
            $migrationsOptions['--uploads'] = $this->option('uploads');
        }

        if ($this->optionHasValue('int')) {
            $migrationsOptions['--int'] = $this->option('int');
        }

        if ($this->optionHasValue('bool')) {
            $migrationsOptions['--bool'] = $this->option('bool');
        }
        
        if ($this->optionHasValue('double')) {
            $migrationsOptions['--double'] = $this->option('double');
        }
        if ($this->optionHasValue('table')) {
            $migrationsOptions['--table'] = $this->option('table');
        }

        if (isset($this->info['parent'])) {
            $migrationsOptions['--parent'] = $this->info['parent'];
        }

        Artisan::call('engez:migration', $migrationsOptions);
    }

    /**
     * Generate routes files
     * 
     * @return void
     */
    protected function createRoutes()
    {
        if ($this->optionHasValue('type')) {
            $type = $this->option('type');
        } else {
            $type = $this->info['type'];
        }

        if (isset($this->info['parent'])) {
            return $this->updateRoutes();
        }
        
        // create routes directory
        $content = File::get($this->path("Controllers/Site/controller.php"));

        $routesDirectory = $this->modulePath("routes");

        $this->checkDirectory($routesDirectory);


        $controller = $this->info['controller'];

        $controllerName = basename(str_replace('\\', '/', $controller));

        // replace module name
        $targetModule = $this->info['moduleName'];
        $routeModule  =  $this->info['moduleName'];
        if (isset($this->info['parent'])) {
            $targetModule = str::studly($this->info['parent']) . '\\' . $this->info['moduleName'];
            $routeModule = str::studly($this->info['parent']) . '/' . $this->info['moduleName'];
        }

        if (in_array($type, ['all', 'site'])) {
            // generate the site routes file
            $content = File::get($this->path("routes/site.php"));

            // replace controller name
            $content = str_ireplace("ControllerName", "{$controllerName}Controller", $content);

            // replace module name
            $content = str_ireplace("ModuleName", "{$targetModule}", $content);

            // replace route prefix
            $routePrefix = strtolower($this->module);
            $content = str_ireplace("route-prefix", "{$this->module}", $content);

            // create the route file
            $filePath = $routesDirectory . '/site.php';

            $this->createFile($filePath, $content, 'Site routes');
          
        }

        if (in_array($type, ['all', 'admin'])) {
            // generate the admin routes file
            $content = File::get($this->path("routes/admin.php"));

            // replace controller name
            $content = str_ireplace("ControllerName", "{$controllerName}Controller", $content);
            
            // replace module name
            $content = str_ireplace("ModuleName", "{$targetModule}", $content);
            
            $middleware = "";
            if (in_array('users', Mongez::getStored('modules'))){
                $middleware = "'logged-in','check-permission'"; 
            } 

            // Set middleware list
            $content = str_ireplace("middlewareList", $middleware, $content);
            
            // replace route prefix
            $routePrefix = strtolower($this->info['moduleName']);
            $content = str_ireplace("route-prefix", "{$routePrefix}", $content);

            // create the route file
            $filePath = $routesDirectory . '/admin.php';

            $this->createFile($filePath, $content, 'Admin routes');
        }        
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

        $controllerName = basename(str_replace('\\', '/', $controller));

        // replace module name
        $routeModule  =  strtolower($this->info['moduleName']);
        if (in_array($type, ['all', 'site'])) {
            
            // generate the site routes file
            
            $content = File::get($this->modulePath("routes/site.php"));
            $content = str_replace(
                '// Sub API CRUD routes',
                "// Sub API CRUD routes
    Route::get('/{$this->info['parent']}/{$routeModule}/{id}','{$controllerName}Controller@index');
    Route::get('/{$this->info['parent']}/$routeModule}/{id}','{$controllerName}Controller@show');",
                $content
            );
            File::put($this->modulePath("routes/site.php"),$content);
        }

        if (in_array($type, ['all', 'admin'])) {
            $content = File::get($this->modulePath("routes/site.php"));
            $content = str_replace(
                '// Sub API CRUD routes',
                "// Sub API CRUD routes
    Route::get('/{$this->info['parent']}/{$routeModule}','{$controllerName}Controller@index');
    Route::get('/{$this->info['parent']}/{$routeModule}/{id}','{$controllerName}Controller@show');",
                $content
            );

            File::put($this->modulePath("routes/site.php"),$content);
            $content = File::get($this->modulePath("routes/admin.php"));
            $content = str_replace(
                '// Sub API CRUD routes',
                "// Sub API CRUD routes
    Route::apiResource('/{$this->info['parent']}/{$routeModule}', '{$controllerName}Controller');",
                $content
            );
            File::put($this->modulePath("routes/admin.php"),$content);
        }
        return;     
    }

    /**
     * 
     */
    protected function setOptions(array $keys)
    {
        $neededOptions = [];
        foreach ($keys as $index => $key ) 
        {
            if (is_numeric($index)) {
                $index = $key;
            }

            if (!str::startsWith('--' ,$index)) {
                $index = '--' .$index;
            }

            if ($this->optionHasValue($key)) {
                $neededOptions[$index] = $this->option($key);
            }
        }
        return $neededOptions;
    }

    /**
     * Add routes to permission table
     * 
     * @return void 
     */
    public function addRoutesToPermissionTable()
    {
        $permissionsRepo = repo('permissions');
        $permissionsRepo->insertModulePermissions($this->moduleName);
    }

    /**
     * Update configurations
     *
     * @return void
     */
    protected function updateConfig(): void
    {
        $config = File::get($mongezPath =  base_path('config/mongez.php'));

        $replacementLine = '// Auto generated repositories here: DO NOT remove this line.';

        if (!Str::contains($config, $replacementLine)) return;

        $repositoryClassName = basename(str_replace('\\', '/', $this->info['repository']));

        $repositoryShortcut = $this->repositoryShortcutName($this->info['repository']);
        
        $module = $this->info['moduleName'];
        if (isset($this->info['parent'])) {
            $module = Str::studly($this->info['parent']);
        }

        $replacedString = "'{$repositoryShortcut}' => App\\Modules\\$module\\Repositories\\{$repositoryClassName}Repository::class,\n \t\t $replacementLine";
        $updatedConfig = str_replace($replacementLine, $replacedString, $config);

        config(['mongez.repositories.' .$repositoryShortcut => "App\\Modules\\$module\\Repositories\\{$repositoryClassName}Repository"]);

        File::put($mongezPath, $updatedConfig);
    }
    
    /**
     * Update configurations
     *
     * @return void
     */
    protected function updateServiceProviderConfig(): void
    {
        $config = File::get($mongezPath =  base_path('config/app.php'));

        $replacementLine = '// Auto generated providers here: DO NOT remove this line.';

        if (!Str::contains($config, $replacementLine)) return;
        
        $module = $this->info['moduleName'];
        $serviceProviderClassName = Str::singular($module) .'ServiceProvider';

        $replacedString = "App\\Modules\\$module\\Providers\\{$serviceProviderClassName}::class,\n \t\t$replacementLine";
        $updatedConfig = str_replace($replacementLine, $replacedString, $config);

        File::put($mongezPath, $updatedConfig);
    }
}