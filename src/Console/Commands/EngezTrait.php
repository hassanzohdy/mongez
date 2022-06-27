<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use HZ\Illuminate\Mongez\Console\EngezInterface;
use HZ\Illuminate\Mongez\Console\EngezGeneratorCommand;

class EngezTrait extends EngezGeneratorCommand implements EngezInterface
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:trait {trait} 
                                        {--module=} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new trait to the given module';

    /**
     * The resource name.
     * 
     * @var string
     */
    protected string $traitName;

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

        $this->info('Trait has been created successfully');
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

        $this->traitName = $this->argument('trait');
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
            // trait name
            '{{ TraitName }}' => $this->traitName,
        ];

        $this->putFile("Traits/{$this->traitName}.php", $this->replaceStub('Traits/trait', $replacements));
    }
}
