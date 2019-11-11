<?php
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

if (! function_exists('pre')) {
    /**
     * print the given variable
     * 
     * @param mixed $var
     * @return void
     */
    function pre($var)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
}

if (! function_exists('pred')) {
    /**
     * print the given variable then stop the script execution
     * 
     * @param mixed $var
     * @return void
     */
    function pred($data)
    {
        pre($data);
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