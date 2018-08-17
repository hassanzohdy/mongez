<?php

use HZ\Laravel\Organizer\App\Contracts\RepositoryInterface;
use HZ\Laravel\Organizer\App\Exceptions\NotFoundRepositoryException;

if (! function_exists('user')) {
    /**
     * Get current user object
     * 
     * @return mixed
     */
    function user()
    {
        // this is used for now for api routes
        // adjust it to suit your needs
        return Auth('api')->user();
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
     * @return \HZ\Laravel\Organizer\App\Contracts\RepositoryInterface
     * @throws \HZ\Laravel\Organizer\App\Exceptions\NotFoundRepositoryException
     */
    function repo(string $repository): RepositoryInterface
    {
        $repositoryClass = config('organizer.repositories.' . $repository);

        if (! $repositoryClass) {
            throw new NotFoundRepositoryException(sprintf('Call to undefined repository: %s', $repository));
        }

        return App::make($repositoryClass);
    }
}

if (! function_exists('array_remove')) {
    /**
     * Remove from array by the given value
     * 
     * @param mixed $value
     * @param array $array
     * @return void
     */
    function array_remove_by_value($value, array &$array)
    {
        return Arr::remove($value, $array);
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
    function str_remove_first($needle, string $object)
    {
        return Str::removeFirst($needle, $object);
    }
}