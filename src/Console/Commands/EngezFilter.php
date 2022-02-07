<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use HZ\Illuminate\Mongez\Console\EngezInterface;
use HZ\Illuminate\Mongez\Console\EngezGeneratorCommand;

class EngezFilter extends EngezGeneratorCommand implements EngezInterface
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:filter {filter} 
                                        {--module=} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new filter to the given module';

    /**
     * The database name 
     * 
     * @var string
     */
    protected string $filterName;

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

        $this->info('Filter has been created successfully');
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

        $this->filterName = $this->filterClass($this->argument('filter'));
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
            // filter class name
            '{{ FilterClassName }}' => $this->filterName,
            // replace database name 
            '{{ DatabaseName }}' => $this->getDatabaseName(),
        ];

        $this->putFile("Filters/{$this->filterName}.php", $this->replaceStub('Filters/filter', $replacements), 'Filter');
    }
}
