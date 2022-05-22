<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use HZ\Illuminate\Mongez\Console\EngezInterface;
use HZ\Illuminate\Mongez\Console\EngezGeneratorCommand;

class EngezRequest extends EngezGeneratorCommand implements EngezInterface
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:request {request} 
                                        {--module=} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new request to the given module';

    /**
     * The resource name.
     * 
     * @var string
     */
    protected string $requestName;

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

        $this->info('Resource has been created successfully');
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

        $this->requestName = $this->argument('request');
    }

    /**
     * Create Model 
     *
     * @return void
     */
    public function create()
    {
        $replacements = [
            // module name
            '{{ ModuleName }}' => $this->getModule(),
            // request class name
            '{{ RequestName }}' => $this->requestName,
        ];

        $this->putFile("Requests/{$this->requestName}.php", $this->replaceStub('Requests/request', $replacements));
    }
}
