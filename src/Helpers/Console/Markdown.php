<?php
namespace HZ\Illuminate\Mongez\Helpers\Console;

use File;
use Illuminate\Support\Str;
use HZ\Illuminate\Mongez\Traits\Console\EngezTrait;

class Markdown
{
    use EngezTrait;
    
    /**
     * Module Name
     * 
     * @var string
     */
    protected $moduleName;

    /**
     * Module data
     * 
     * @var array
     */
    protected $data = [];

    /**
     * MarkDown data
     * 
     * @var string
     */
    protected $content;

    /**
     * For setting required data to generate MarkDown file
     * 
     * @param array $data  
     */
    public function __construct(array $data)
    {
        $this->prepareData($data); 
        $this->init();
    }

    /**
     * prepare and set needed data.
     * 
     * @param array $data
     * @return void
     */
    protected function prepareData($data)
    {
        $this->moduleName = $data['moduleName'];
        $this->data = $data['data'];
        if (isset($data['parent'])) {
            $this->info['parent'] = $data['parent'];
            $this->info['moduleName'] = $data['moduleName'];
            $this->updateParentDocs();
        }
    }
    
    /**
     * Set Postman details. 
     * 
     * @return void
     */
    protected function init() 
    {   
        $content = File::get($this->path("docs/module-docs.md"));
        
        // replace postman name
        $content = str_ireplace("moduleName", $this->moduleName, $content);
        // replace base url
        $content = str_ireplace("baseUrl", url('/'), $content);
        
        $content = str_ireplace("routeName", strtolower(str::plural($this->moduleName)), $content);
        
        $data = '{'.PHP_EOL;
        
        foreach ($this->data as $key => $value) {
            $value = '"'.$value.'"';
            $data .= '"'.$key.'"'.' : ' .$value .PHP_EOL;
        }
        $data .= '}';

        $content = str_ireplace("data", $data, $content);

        return $this->content = $content;
    }

    /**
     * 
     */
    protected function updateParentDocs()
    {
        $content = File::get($this->modulePath("docs/README.md"));
        
        $moduleName = strtolower($this->moduleName);
        // replace postman name
        $content = str_ireplace("# Other resources", "# Other resources\n[{$this->moduleName}](./{$moduleName}.md)", $content);
        File::put($this->modulePath("docs/README.md"), $content);
    }

    /**
     * Get content of postman json file
     * 
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
}