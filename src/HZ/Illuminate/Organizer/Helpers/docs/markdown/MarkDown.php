<?php
namespace HZ\Illuminate\Organizer\Helpers\docs\markdown;

use File;
use Illuminate\Support\Str;
use HZ\Illuminate\Organizer\Traits\Console\EngezTrait;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

class MarkDown
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
    public function __construct($data)
    {
        $this->moduleName = $data['moduleName'];
        $this->data = $data['data']; 
        $this->setMarkDown();
    }

    /**
     * Set Postman details. 
     * 
     * @return void
     */
    protected function setMarkDown() 
    {   
        $content = File::get($this->path("Docs/moduleDocs/README.md"));
        
        // replace postman name
        $content = str_ireplace("moduleName", $this->moduleName, $content);
        
        // replace base url
        $content = str_ireplace("baseUrl", url('/'), $content);
        
        $content = str_ireplace("routeName", strtolower(str::plural($this->moduleName)), $content);
        
        $data = '{
';
        foreach ($this->data as $key)
        {
            $data .= '"'.$key.'"'.' : '.'"text",
';
        }
        $data.='}';
        
        $content = str_ireplace("data", $data, $content);

        return $this->content = $content;
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