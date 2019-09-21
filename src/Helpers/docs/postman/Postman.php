<?php
namespace HZ\Illuminate\Mongez\Helpers\docs\postman;

use File;
use Illuminate\Support\Str;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\json_decode;
use HZ\Illuminate\Mongez\Traits\Console\EngezTrait;

class Postman
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
    protected $data;

    /**
     * Postman data
     * 
     * @var string
     */
    protected $content;

    /**
     * Set needed data.
     * 
     * @param array $data
     * @return void  
     */
    public function __construct($data)
    {
        $this->moduleName = $data['moduleName'];
        
        $this->data = [];
        foreach ($data['data'] as $input) {
            $this->data [] = [
                'key' => $input,
                'type' => 'text',
            ];
        }
        $this->setPostman();
    }

    /**
     * Set Postman details. 
     * 
     * @return void
     */
    protected function setPostman() 
    {   
        $content = File::get($this->path("Docs/postman/module.postman.json"));        
        
        // replace postman name
        $content = str_ireplace("{postmanName}", $this->moduleName.' Module', $content);
        
        // replace module name
        $content = str_ireplace("{moduleName}", $this->moduleName, $content);

        // replace base url
        $content = str_ireplace("{baseUrl}", url('/'), $content);
        // replace routeUri
        $content = str_ireplace("{routeUri}", strtolower(str::plural($this->moduleName)), $content);
        
        $content = json_decode($content);

        // Set request details 
        foreach ($content->item as $item) 
        {
            // set parameters of Add and update request
            if ($item->request->method == 'POST') {
                $item->request->body->formdata = json_decode(json_encode($this->data), false); 
            } elseif ($item->request->method == 'PUT') {
                $item->request->body->urlencoded = json_decode(json_encode($this->data), false);
            }

            // Set host name to request
            $item->request->url->host = request()->getHost();
            $defaultPath = $item->request->url->path;

            // Set path to request 
            if (in_array("{id}", $defaultPath)) {
                $defaultPath [count($defaultPath)-1] = strtolower(str::plural($this->moduleName));
                $defaultPath [] = "{id}";
            } else {
                $defaultPath [] = strtolower(str::plural($this->moduleName));
            }
            $path = explode("/", request()->path());
            $path = array_merge($path, $defaultPath);
            $item->request->url->path = array_values(array_filter(array_unique($path)));
            
            // Set protocol to request.  
            $protocol = preg_replace('/[0-9]+/', '', request()->getProtocolVersion());
            $protocol = str_ireplace("/.", '', $protocol);
            $item->request->url->protocol = strtolower($protocol);                
        }
    
        $this->content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get content of file.
     * 
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
}