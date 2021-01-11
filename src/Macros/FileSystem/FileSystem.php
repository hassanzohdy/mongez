<?php
namespace HZ\Illuminate\Mongez\Macros\FileSystem;

class FileSystem
{
    /**
     * Get json content.  
     * 
     * @param string $path
     * @param bool $assoc
     * @return array|stdClass
     */
    public function getJson()
    { 
        return function ($path, $assoc = true) {
            $content = $this->get($path);

            if (! $content) return [];

            return json_decode($content, $assoc);    
        };
    }
    
    /**
     * Put json content.  
     * 
     * @param string $path
     * @param array|object $content
     * @param int $flags
     * @return void
     */
    public function putJson()
    { 
        return function (string $path, $content, $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) {
            $this->put($path, json_encode($content, $flags));
        };
    }
}
