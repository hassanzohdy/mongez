<?php

namespace HZ\Illuminate\Mongez\Traits\Utils;

use Illuminate\Support\Str;

trait TextUtils
{
    /**
     * Create studly case string for given string
     * 
     * @param string $text
     * @return string
     */
    protected function studly(string $text): string
    {
        return Str::studly($text);
    }

    /**
     * Create kebab case string for given string
     * 
     * @param string $text
     * @return string
     */
    protected function kebab(string $text): string
    {
        return Str::kebab($text);
    }

    /**
     * Create snake case string for given string
     * 
     * @param string $text
     * @return string
     */
    protected function snake(string $text): string
    {
        return Str::snake($text);
    }

    /**
     * Create camel case string for given string
     * 
     * @param string $text
     * @return string
     */
    protected function camel(string $text): string
    {
        return Str::camel($text);
    }

    /**
     * Create singular string for given string
     * 
     * @param string $text
     * @return string
     */
    protected function singular(string $text): string
    {
        return Str::singular($text);
    }

    /**
     * Create plural string for given string
     * 
     * @param string $text
     * @return string
     */
    protected function plural(string $text): string
    {
        return Str::plural($text);
    }

    /**
     * Create plural string for given string
     * 
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    protected function startsWith(string $haystack, string $needle): bool
    {
        return Str::startsWith($haystack, $needle);
    }
    

    /**
     * Create plural string for given string
     * 
     * @param string $haystack
     * @param string|string[] $needle
     * @return bool
     */
    protected function contains(string $haystack, $needle): bool
    {
        return Str::contains($haystack, $needle);
    }
}
