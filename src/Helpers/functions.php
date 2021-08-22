<?php
declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use HZ\Illuminate\Mongez\Exceptions\NotFoundRepositoryException;
use HZ\Illuminate\Mongez\Contracts\Repositories\RepositoryInterface;

if (! function_exists('user')) {
    /**
     * Get current user object
     * 
     * @return mixed
     */
    function user($guard = null)
    {
        return request()->user($guard);
    }
}

if (! function_exists('toJson')) {
    /**
     * Encode the given data into a json content with unescaped slashes, pretty print and unescape unicode
     * 
     * @param mixed $data
     * @return string
     */
    function toJson($data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

if (! function_exists('pre')) {
    /**
     * print the given data
     * 
     * @param mixed ...$vars
     * @return void
     */
    function pre(...$vars)
    {
        foreach ($vars as $var) {
            echo '<pre>';
            print_r($var);
            echo '</pre>';
        }
    }
}

if (! function_exists('pred')) {
    /**
     * print the given dta then stop the script execution
     * 
     * @param mixed ...$vars
     * @return void
     */
    function pred(...$vars)
    {
        pre(...$vars);
        die();
    }
}

if (! function_exists('repo')) {
    /**
     * Get repository object for the given repository name
     * 
     * @param string $repository
     * @return \HZ\Illuminate\Mongez\Contracts\RepositoryInterface
     * @throws \HZ\Illuminate\Mongez\Exceptions\NotFoundRepositoryException
     */
    function repo(string $repository): RepositoryInterface
    {
        static $repos = [];
        
        if (! empty($repos[$repository])) {
            return $repos[$repository];
        }
        
        $repositoryClass = config('mongez.repositories.' . $repository); 
        
        if (! $repositoryClass) { 
            throw new NotFoundRepositoryException(sprintf('Call to undefined repository: %s', $repository)); 
        }
       
        $repositoryClass = App::make($repositoryClass);

        return $repos[$repository] = $repositoryClass; 
    }
}

if (! function_exists('array_remove')) {
    /**
     * Remove from array by the given value
     * 
     * @param  mixed $value
     * @param  array $array
     * @param  bool $removeFirstOnly
     * @return array
     */
    function array_remove($value, array $array, bool $removeFirstOnly = false): array
    {
        return Arr::remove($value, $array, $removeFirstOnly);
    }
}

if (! function_exists('str_remove_first')) {
    /**
     * Remove from the given object the first occurrence for the given needle
     * 
     * @param mixed $needle
     * @param string $object
     * @return string
     */
    function str_remove_first(string $needle, string $object)
    {
        return Str::removeFirst($needle, $object);
    }
}