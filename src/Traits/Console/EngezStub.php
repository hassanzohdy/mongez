<?php

namespace HZ\Illuminate\Mongez\Traits\Console;

use Illuminate\Support\Str;
use HZ\Illuminate\Mongez\Helpers\Console\Stub;

trait EngezStub
{
    /**
     * Get stub content
     * 
     * @param string path
     * @return string
     */
    protected function getStub(string $path): string
    {
        if (!Str::endsWith($path, '.php')) {
            $path .= '.stub';
        }

        return $this->files->get($this->path($path));
    }

    /**
     * Create a new stub instance for more complex updates
     * 
     * @param string $path
     * @return Stub
     */
    protected function stubInstance(string $path): Stub
    {
        if (!Str::endsWith($path, '.php')) {
            $path .= '.stub';
        }

        return new Stub($this->path($path), $this);
    }

    /**
     * Convert the given data as array syntax
     * 
     * @param array|string $data
     * @return string
     */
    protected function stubStringAsArray($data): string
    {
        if (is_string($data)) {
            $data = explode(',', $data);
        }

        $string = '';

        // $isAssociativeArray = false;

        foreach ($data as $index => $value) {
            if (is_numeric($index)) {
                $string .= "'" . $value . "',";
            } else {
                // $isAssociativeArray = true;
                $string .= "'" . $index . "' => '" . $value . "',";
            }
        }

        $string = rtrim($string, ',');

        // if ($isAssociativeArray) {
        //     return '[' . PHP_EOL . $string . PHP_EOL . ']';
        // } else {
        // }
        return '[' . $string . ']';
    }

    /**
     * Get the content of the given stub path
     * then replace all stubs inside it with the given values
     * 
     * @param  string $stubPath
     * @param  array $replacements
     * @return string
     */
    protected function replaceStub(string $stubPath, array $replacements): string
    {
        $content = $this->getStub($stubPath);

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
    }

    /**
     * Return proper value for default data that will be replaced
     * If the given data is empty, then return tab indent with double slash
     * 
     * @param  array $data
     * @param  string $defaultValue
     * @return string
     */
    protected function stubData(array $data, string $defaultValue = ''): string
    {
        return $data ? implode(PHP_EOL, $data) : $defaultValue;
    }

    /**
     * Add tab indent then append the given text
     * 
     * @param string $text
     * @return string
     */
    protected function tabWith(string $text): string
    {
        return "\t" . $text;
    }
}
