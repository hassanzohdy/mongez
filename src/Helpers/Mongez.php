<?php
namespace HZ\Illuminate\Mongez\Helpers;

use File;
use Illuminate\Support\Arr;

class Mongez
{

    /**
     * Root of mongez file in storage directory
     *
     * @const string
     */
    const MONGEZ_STORAGE_DIRECTORY = 'mongez';

    /**
     * Mongez file name in stored directory.
     *
     * @const string
     */
    const MONGEZ_STORAGE_FILE_NAME = 'mongez.json';

    /**
     * Mongez default content
     * 
     * @const array
     */
    const MONGEZ_STORAGE_FILE_DEfAULT_CONTENT = [
        'installed' => true,
        'modules' => [],
    ];

    /**
     * Mongez File path.
     *
     * @var string
     */
    protected static $mongezFilePath;

    /**
     * Mongez file content
     * 
     * @var array
     */
    protected static $mongezContent;

    /**
     * Prepare the Mongez Console
     * Create Mongez storage directory.
     *
     * @return void
     */
    public static function init()
    {
        static::$mongezFilePath = static::getMongezStorageDirectory() . '/' . static::MONGEZ_STORAGE_FILE_NAME;           
    }

    /**
     * Check if package is installed
     * 
     * @return bool
     */
    public static function isInstalled(): bool
    {
        return File::isFile(static::$mongezFilePath);
    }

    /**
     * Prepare the package for the first time 
     * 
     * @return void
     */
    public static function install()
    {
        File::MakeDirectory(static::getMongezStorageDirectory(), 0777);

        File::put(static::getMongezStorageFilePath(), json_encode(static::MONGEZ_STORAGE_FILE_DEfAULT_CONTENT, JSON_PRETTY_PRINT));             
    }

    /**
     * Get mongez file path.
     *
     * @return string
     */
    protected static function getMongezStorageFilePath()
    {
        return static::$mongezFilePath;
    }

    /**
     * Get Mongez storage directory.
     *
     * @return string
     */
    protected static function getMongezStorageDirectory()
    {
        return storage_path(static::MONGEZ_STORAGE_DIRECTORY);
    }

    /**
     * Set storage file content.
     * 
     * @array $array  
     */
    protected static function setStorageFileContent(array $content)
    {
        File::putJson(static::getMongezStorageFilePath(), $content);
    }

    /**
     * Get value from mongez config file by key.
     *
     * @return mixed
     */
    public static function getStored($key)
    {
        if (! static::$mongezContent) {
            static::$mongezContent = static::getStorageFileContent();
        }
        return Arr::get(static::$mongezContent, $key);
    }

    /**
     * Get all stored config data.
     *
     * @return mixed
     */
    protected static function getStorageFileContent()
    {
        return File::getJson(static::$mongezFilePath);
    }

    /**
     * Update value of config key.
     *
     * @param string $key.
     * @param string $value.
     * @return mixed
     */
    public static function setStored($key, $value)
    {
        static::$mongezContent[$key] = $value;
    }

    /**
     * Update storage file 
     * 
     * @return void 
     */
    public static function updateStorageFile()
    {
        static::setStorageFileContent(static::$mongezContent);
    }

    /**
     * Get the package path
     * 
     * @param string $path 
     * @return string
     */
    public static function packagePath($path='')
    {
        return dirname(__DIR__, 2) . '/' . ltrim($path, '/');
    }
}