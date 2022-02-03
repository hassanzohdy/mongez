<?php

namespace HZ\Illuminate\Mongez\Traits\Console;

use HZ\Illuminate\Mongez\Helpers\Mongez;
use HZ\Illuminate\Mongez\Traits\Utils\TextUtils;

trait ModuleData
{
    /**
     * Text Helpers
     */
    use TextUtils;

    /**
     * Module Name
     * 
     * @var string
     */
    protected string $moduleName;

    /**
     * Repository Name
     * 
     * @var string
     */
    protected string $repositoryName;

    /**
     * Module Build mode
     * Available Values api|ui
     * 
     * @var string
     */
    protected string $buildMode;

    /**
     * Prepare data
     * 
     * @return void
     */
    protected function prepareData()
    {
        $this->buildMode = $this->optionHasValue('build') ? $this->option('value') : $this->config('build', 'api');
    }

    /**
     * Determine if the current build mode is api mode
     * 
     * @return bool
     */
    protected function isApiMode(): bool
    {
        return $this->buildMode === 'api';
    }

    /**
     * Determine if the current build mode is ui mode
     * 
     * @return bool
     */
    protected function isUINode(): bool
    {
        return $this->buildMode === 'ui';
    }

    /**
     * Set Module Name
     * 
     * @param string $moduleName
     * @return void
     */
    protected function setModuleName(string $moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * Get module name
     * The module name MUST BE in plural with studly case format
     * 
     * @return string
     */
    protected function getModule(): string
    {
        return $this->plural(
            $this->studly($this->moduleName)
        );
    }

    /**
     * Get singular module studly case text
     * 
     * @return string
     */
    protected function singularModule(): string
    {
        return $this->singular($this->getModule());
    }

    /**
     * Return a full data types options with the given options
     * If second parameter is not empty, then its value will be taken as well from
     * the passed options list
     * 
     * @param  array $options
     * @param  array  $moreOptions
     * @return array
     */
    protected function withDataTypes(array $options, array $moreOptions = []): array
    {
        $optionsList = $this->optionsValues(array_merge(
            static::DATA_TYPES,
            $moreOptions,
        ));

        return array_merge($options, $optionsList);
    }

    /**
     * Determine if the given module name exists in modules list
     * 
     * @param string $moduleName
     * @return bool
     */
    protected function moduleExists(string $moduleName = ''): bool
    {
        return in_array(
            strtolower($moduleName ?: $this->getModule()),
            array_map('strtolower', Mongez::getStored('modules'))
        );
    }
}
