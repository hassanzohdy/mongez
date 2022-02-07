<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use HZ\Illuminate\Mongez\Console\EngezInterface;
use HZ\Illuminate\Mongez\Console\EngezGeneratorCommand;

class EngezTranslation extends EngezGeneratorCommand implements EngezInterface
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:translation {file} 
                                        {--module=} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new translation file to the given module';

    /**
     * The translation file name 
     * 
     * @var string
     */
    protected string $fileName;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->init();
        $this->validateArguments();
        $this->create();

        $this->info('Translation Files have been created successfully');
    }

    /**
     * Prepare data
     * 
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->setModuleName($this->option('module'));

        $this->fileName = $this->kebab($this->argument('file'));
    }

    /**
     * Create Model 
     *
     * @return void
     */
    public function create()
    {
        $moduleName = $this->getModule();

        $replacements = [
            // module name
            '{{ ModuleName }}' => $moduleName,
            // file name
            '{{ fileName }}' => $this->fileName,
            // module translation key
            '{{ moduleTranslationKey }}' => $this->kebab($moduleName),
            // Translation Utils class
            '{{ TranslationUtilsClass }}' => $moduleName . 'Utils',
        ];

        $localeCodes = (array) config('mongez.localeCodes') ?: [config('app.locale')];

        foreach ($localeCodes as $localeCode) {
            $this->putFile("lang/{$localeCode}/{$this->fileName}.php", $this->replaceStub('lang/file', $replacements), 'Translation');
        }

        // check the utils class
        $this->buildUtilsClass();
    }
}
