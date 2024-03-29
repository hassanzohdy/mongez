<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use HZ\Illuminate\Mongez\Helpers\Mongez;

class PostmanCollection extends Command
{
    /**
     * Path Folder of modules
    */
    const MODULES_PATH = 'app/Modules';

    /**
     * Docs Folder Name
    */
    const DOCS_DIRECTORY_NAME = 'docs/postman';

    /**
     * default generate file name
    */
    const DEFAULT_GENERATED_FILE_NAME = 'project-api-collection';

    /**
     * Postman Files.
     *
     * @var array
     */
    private $postmanFiles = [];

    /**
     * Content of each postman file module.
     *
     * @var array
     */
    private $filesContent = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:postman {--fileName=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate one postman collection of all modules.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->loadPostmanFiles();

        $this->loadContentPostmanFiles();

        $this->createCollection();

        return 0;
    }

    /**
     * load all postman files 
     * 
     * @return void
    */
    private function loadPostmanFiles() 
    {
        $modulePath = $this->getModulePath();

        $this->postmanFiles = array_filter(glob($modulePath . '/*/' . self::DOCS_DIRECTORY_NAME . '/*.postman.json'));

        $this->info('Successfully Loaded Postman Files...');
    }

    /**
     * Load Content of postman file
     * 
     * @return void
    */
    private function loadContentPostmanFiles()
    {
        foreach($this->postmanFiles as $postmanFile) {
            $content = $this->pareFileToJson($postmanFile);

            $name = $content['info']['name'];
            
            $this->filesContent[] = $content;

            $this->info("Collection [ $name ] was successfully added.");
        }
    }

    /**
     * Create Root Collection
     * 
     * @return void
    */
    private function createCollection()
    {
        $appName = env('APP_NAME', 'Postman Collection with Mongez');

        $collection = [
            'item' => [],
            'variable' => [],
            'info' => [
                'name' => $appName,
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            ]
        ];

        foreach($this->filesContent as $content) {
            // Remove Content of Globel Variable in each Collection  
            // will use it in Root Collection Only
            $content['variable'] = [];

            $content['name'] = $content['info']['name'];

            $collection['item'][] = $content;
        }

        $collection['variable'] = $this->getGlobalVariables();

        $fileName = $this->optionHasValue('fileName') ? $this->option('fileName') : self::DEFAULT_GENERATED_FILE_NAME;
        
        if(!File::exists($postmanDirectory = public_path('/postman'))) {
            File::makeDirectory($postmanDirectory, 0755, true, true);
        }

        $fileName = $fileName . "-v{$this->newVersion()}";

        file_put_contents($postmanDirectory . "/$fileName.postman.json", json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $this->info("Success Generate Postman Collection for ' $appName '");
    }

    /**
     * Get Global Variables
     * 
     * @return array
    */
    private function getGlobalVariables()
    {
        $allGlobalVariables = [];

        foreach(array_column($this->filesContent, 'variable') as $globalVariable) {
            foreach($globalVariable as $variable) {
                $allGlobalVariables[$variable['key']] = $variable;
            }
        }

        $allGlobalVariables = array_merge($allGlobalVariables, $this->getConfigGlobalVariables());

        return array_values($allGlobalVariables);
    }

    /**
     * Get global variables from config
     * 
     * @return array
    */
    private function getConfigGlobalVariables()
    {
        $allGlobalVariables = [];

        foreach(config('mongez.postman.variables') as $key => $value) {
            $allGlobalVariables[$key] = [
                'key' => $key,
                'value' => $value
            ];
        }
        
        return $allGlobalVariables;
    }


    

    /**
     * parsing file from text to json
     * 
     * @param string $file
     * @return array
    */
    private function pareFileToJson($file)
    {
        $content = file_get_contents($file);

        return json_decode($content, true);
    }
    
    /**
     * Get Module Path
     * 
     * @return string
    */
    private function getModulePath()
    {
        return base_path(self::MODULES_PATH);
    }

    /**
     * Update postman veriosn in config and return new version
     * 
     * @return float
     */
    private function newVersion()
    {
        $oldVersion = Mongez::getStored('postmanVersion');

        $newVersion = number_format(round($oldVersion + 0.1, 1), 1, '.', '');

        Mongez::setStored('postmanVersion', "$newVersion");

        Mongez::updateStorageFile();

        return $newVersion;
    }
}