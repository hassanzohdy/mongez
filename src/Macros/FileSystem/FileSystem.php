<?php
namespace HZ\Illuminate\Mongez\Macros\FileSystem;

class FileSystem
{
    /**
     * Get json content.  
     * 
     * @param string $path
     * @return array
     */
    public function getJson()
    { 
        return function ($path, $assoc = true) {
            $content = $this->get($path);
            return json_decode($content, $assoc);    
        };
    }
    
    /**
     * Put json content.  
     * 
     * @param array  $content
     * @param string $jsonOption
     * @param string $path
     * @return void
     */
    public function putJson()
    { 
        return function ($path, $content, $jsonOption=JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) {
            $this->put($path, json_encode($content, $jsonOption));
        };
    }
}
