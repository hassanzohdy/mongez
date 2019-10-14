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
        $type = $this->option('type');
        
        if (isset($this->info['parent'])) {
            return $this->updateRoutes();
        }
        // create routes directory
        $content = File::get($this->path("Controllers/Site/controller.php"));

        $routesDirectory = $this->modulePath("routes");

        $this->checkDirectory($routesDirectory);

        // get the content of the api routes file
        $apiRoutesFileContent = File::get(base_path('routes/api.php'));

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
            $content = str_ireplace("route-prefix", "{$this->module}", $content);

            // create the route file
            $filePath = $routesDirectory . '/site.php';

            $this->createFile($filePath, $content, 'Site routes');

            // add the routes file to the api routes file content
            if (Str::contains($apiRoutesFileContent, '// end of site routes')) {
                $apiRoutesFileContent = str_replace(
                    '// end of site routes',
                    "// {$routeModule} module
include base_path('app/Modules/{$routeModule}/routes/site.php');

// end of site routes",
                    $apiRoutesFileContent
                );
            }
        }

        if (in_array($type, ['all', 'admin'])) {
            // generate the admin routes file
            $content = File::get($this->path("routes/admin.php"));

            // replace controller name
            $content = str_ireplace("ControllerName", "{$controllerName}Controller", $content);

            // replace module name
            $content = str_ireplace("ModuleName", "{$targetModule}", $content);

            // replace route prefix
            $content = str_ireplace("route-prefix", "{$this->module}", $content);

            // create the route file
            $filePath = $routesDirectory . '/admin.php';

            $this->createFile($filePath, $content, 'Admin routes');
            // add the routes file to the api routes file content
            if (Str::contains($apiRoutesFileContent, '// end of admin routes')) {
                $apiRoutesFileContent = str_replace(
                    '// end of admin routes',
                    "// {$routeModule} module
    include base_path('app/Modules/{$routeModule}/routes/admin.php');

    // end of admin routes",
                    $apiRoutesFileContent
                );
            }
        }

        // echo($apiRoutesFileContent);

        File::put(base_path('routes/api.php'), $apiRoutesFileContent);
    }
    
    /**
     * update parent routes
     * 
     * @return void
     */
    protected function updateRoutes()
    {
        $type = $this->option('type');

        // get the content of the api routes file
        $apiRoutesFileContent = File::get(base_path('routes/api.php'));
        
        $controller = $this->info['controller'];

        $controllerName = basename(str_replace('\\', '/', $controller));

        // replace module name
        $routeModule  =  strtolower($this->info['moduleName']);
        if (in_array($type, ['all', 'site'])) {
            
            // generate the site routes file
            
            $content = File::get($this->modulePath("routes/site.php"));
            $content = str_replace(
                '// Child routes',
                "Route::get('/{$this->info['parent']}/{$routeModule}/{id}','{$controllerName}Controller@index');
    Route::get('/{$this->info['parent']}/$routeModule}/{id}','{$controllerName}Controller@show');
    // Child routes",
                $content
            );
            File::put($this->modulePath("routes/site.php"),$content);
        }
        if (in_array($type, ['all', 'admin'])) {

            $content = File::get($this->modulePath("routes/site.php"));
            $content = str_replace(
                '// Child routes',
                "Route::get('/{$this->info['parent']}/{$routeModule}','{$controllerName}Controller@index');
    Route::get('/{$this->info['parent']}/{$routeModule}/{id}','{$controllerName}Controller@show');
    // Child routes",
                $content
            );
            File::put($this->modulePath("routes/site.php"),$content);

            $content = File::get($this->modulePath("routes/admin.php"));
            $content = str_replace(
                '// Child API CRUD routes',
                "Route::apiResource('/{$this->info['parent']}/{$routeModule}', '{$controllerName}Controller');
    // Child API CRUD routes",
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
}