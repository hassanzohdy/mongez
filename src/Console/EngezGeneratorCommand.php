<?php

namespace HZ\Illuminate\Mongez\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use HZ\Illuminate\Mongez\Console\Traits\TextUtils;
use HZ\Illuminate\Mongez\Console\Traits\EngezStub;
use HZ\Illuminate\Mongez\Console\Traits\EngezTrait;
use HZ\Illuminate\Mongez\Console\Traits\ModuleData;
use HZ\Illuminate\Mongez\Console\Traits\SharedNames;
use HZ\Illuminate\Mongez\Console\Traits\DatabaseConcerns;

abstract class EngezGeneratorCommand extends Command implements EngezInterface
{
    /**
     * Stubs Manager
     */
    use EngezStub;

    /**
     * General Helpers
     */
    use EngezTrait;

    /**
     * Data Helpers
     */
    use ModuleData;

    /**
     * Database Helpers
     */
    use DatabaseConcerns;

    /**
     * Text Helpers
     */
    use TextUtils;

    /**
     * Share names between generators
     */
    use SharedNames;

    /**
     * List of data types
     *
     * @const array
     */
    public const DATA_TYPES = [
        'string', 'int',
        'float', 'bool',
        'date', 'uploads',
        'locale',
    ];

    /**
     * List of data types options
     *
     * @const string
     */
    public const DATA_TYPES_OPTIONS = '
    {--date=}
    {--locale=}
    {--int=}
    {--float=}
    {--bool=}
    {--string=}
    {--uploads=}
    ';

    /**
     * Table options list
     *
     * @const array
     */
    public const TABLE_INDEXES = [
        'table',
        'index',
        'geo',
        'unique',
        'primary',
    ];

    /**
     * Table indexes options
     *
     * The geo index is for MongoDB
     *
     * @const string
     */
    public const TABLE_INDEXES_OPTIONS = '
    {--table=}
    {--index=}
    {--geo=}
    {--unique=}
    {--primary=id}
    ';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected Filesystem $files;

    /**
     * Constructor
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Initialize the command and prepare its data
     *
     * @return void
     */
    public function init()
    {
        $this->prepareData();

        $this->prepareDatabase();
    }

    /**
     * Validate The module name
     *
     * @return void
     */
    public function validateArguments()
    {
        if (!$this->optionHasValue('module') && !$this->argumentHasValue('module')) {
            return $this->info('module option is required');
        }

        if (!$this->moduleExists()) {
            return $this->terminate('This module is not available');
        }
    }


    /**
     * Add The given content in the given path in the app module directory
     *
     * @param  string $filePath
     * @param string $content
     * @return void
     */
    protected function putFile(string $filePath, string $content)
    {
        $fileDirectory = $this->modulePath(dirname($filePath));

        $this->makeDirectory($fileDirectory);

        $fullPath = $fileDirectory . '/' . basename($filePath);

        // create the file
        $this->createFile($fullPath, $content, 'Model');
    }

    /**
     * Build utils class
     *
     * @return void
     */
    protected function buildUtilsClass()
    {
        $moduleName = $this->getModule();

        $utilsClass = $moduleName . 'Utils';

        if (!$this->files->exists($this->modulePath($utilsClassFilePath = 'Utils/' . $utilsClass . '.php'))) {
            $utilsReplacements = [
                // module name
                '{{ ModuleName }}' => $moduleName,
                // class name
                '{{ ClassName }}' => $utilsClass,
            ];

            $this->putFile($utilsClassFilePath, $this->replaceStub('Utils/utils', $utilsReplacements), 'Utils');
        }
    }

    /**
     * Get value from config
     *
     * @param string $key
     * @param  mixed $default
     * @return mixed
     */
    protected function config($key, $default = null)
    {
        return config('mongez.console.builder.' . $key, $default);
    }
}
