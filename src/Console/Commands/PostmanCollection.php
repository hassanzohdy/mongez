<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use Illuminate\Console\Command;

class PostmanCollection extends Command
{
    /**
     * Path Folder of modules
    */
    const MODULE_PATH = 'app/Modules';

    /**
     * Docs Folder Name
    */
    const DOCS_FOLDER_NAME = 'docs';

    /**
     * default generate file name
    */
    const DEFAULT_GENERATE_FILE_NAME = 'project-api-collection';

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
    protected $description = 'Generate Root Collection ..';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

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

        $this->postmanFiles = array_filter(glob($modulePath . '/*/' . self::DOCS_FOLDER_NAME . '/*.postman.json'));

        $this->info('Success Load Postman Files ..');
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

        $fileName = $this->optionHasValue('fileName') ? $this->option('fileName') : self::DEFAULT_GENERATE_FILE_NAME;

        file_put_contents(base_path("$fileName.postman.json"), json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

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
            foreach($globalVariable as $variable){
                $allGlobalVariables[$variable['key']] = $variable;
            }
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
        return base_path(self::MODULE_PATH);
    }

    /**
     * Return Module Repo
     * 
     * @return mixed
    */
    public function getRepositry($repo)
    {
        $repositories = config('mongez.repositories');

        return $repositories[$repo] ?? false;
    }
}
