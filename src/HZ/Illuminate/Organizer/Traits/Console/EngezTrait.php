<?php
namespace HZ\Illuminate\Organizer\Traits\Console;

use File;
use Illuminate\Console\Command;
use HZ\Illuminate\Organizer\Helpers\Mongez;

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
        return base_path("app/Modules/{$this->info['moduleName']}/$relativePath");
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
}