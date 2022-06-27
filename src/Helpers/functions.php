<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use HZ\Illuminate\Mongez\Events\Events;
use HZ\Illuminate\Mongez\Repository\RepositoryInterface;
use HZ\Illuminate\Mongez\Repository\NotFoundRepositoryException;

if (!function_exists('is_json')) {
    /**
     * Determine whether the given string is json
     * 
     * @param string $string
     * @return bool
     */
    function is_json(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists('user')) {
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

if (!function_exists('to_json')) {
    /**
     * Encode the given data into a json content with unescaped slashes, pretty print and unescape unicode
     * 
     * @param mixed $data
     * @return string
     */
    function to_json($data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('pre')) {
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

if (!function_exists('events')) {
    /**
     * Get an events instance
     * 
     * @return \HZ\Illuminate\Mongez\Events\Events
     */
    function events(): Events
    {
        static $events;
        if (!$events) {
            $events = app()->make(Events::class);
        }

        return $events;
    }
}

if (!function_exists('pred')) {
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

if (!function_exists('repo')) {
    /**
     * Get repository object for the given repository name
     * 
     * @param string $repository
     * @return \HZ\Illuminate\Mongez\Repository\RepositoryInterface
     * @throws \HZ\Illuminate\Mongez\Repository\NotFoundRepositoryException
     */
    function repo(string $repository): RepositoryInterface
    {
        static $repos = [];

        if (!empty($repos[$repository])) {
            return $repos[$repository];
        }

        $repositoryClass = config('mongez.repositories.' . $repository);

        if (!$repositoryClass) {
            throw new NotFoundRepositoryException(sprintf('Call to undefined repository: %s', $repository));
        }

        $repositoryClass = App::make($repositoryClass);

        return $repos[$repository] = $repositoryClass;
    }
}

if (!function_exists('array_remove')) {
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

if (!function_exists('str_remove_first')) {
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

if (!function_exists('set_request_int_inputs')) {
    /**
     * Set int values from request inputs.
     * Help to validate IDs by convert them to int values.
     * If using ApiFormRequest class you have to pass the request to have the int values.
     *
     * @param array $inputs
     * @param $request
     * @return void
     */
    function set_request_int_inputs(array $inputs, $request = null): void
    {
        $request = $request ?? request();

        $requestKeys = array_unique(array_map(function ($input) {
            return str_contains($input, '.') ? explode('.', $input)[0] : $input;
        }, $inputs));

        $requestInputs = $request->only($requestKeys);

        $requestInputsDotNotation = Arr::dot($requestInputs);

        collect($requestInputsDotNotation)->each(function ($value, $key) use ($inputs, &$requestInputs) {
            $keyArray = str_contains($key, '.') ? explode('.', $key) : [$key];

            $filteredKeyArray = array_filter($keyArray, function ($item) {
                return preg_match_all('!\d+!', $item) === 1;
            });

            $filteredKey = implode('.', array_diff_assoc($keyArray, $filteredKeyArray));

            if (in_array($filteredKey, $inputs) && $value) {
                Arr::set($requestInputs, $key, (int) $value);
            }
        })->toArray();

        $request->merge($requestInputs);
    }
}

if (!function_exists('get_user_repo')) {
    /**
     * Get user repository name based on user type.
     *
     * @param string|null $userType
     * @param bool $appendRepositoryKeyword
     * @return string|array
     * @throws Exception
     */
    function get_user_repo(string $userType = null, bool $appendRepositoryKeyword = true)
    {
        if (!$userType) {
            return config('mongez.userTypes');
        }

        $repository = config("mongez.userTypes.{$userType}");

        if (!$repository) {
            throw new Exception(sprintf('%s is not in config\mongez.userTypes configurations', $userType));
        }

        return $appendRepositoryKeyword ? $repository . 'Repository' : $repository;
    }
}


