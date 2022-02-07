<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use HZ\Illuminate\Mongez\Console\EngezInterface;
use HZ\Illuminate\Mongez\Console\EngezGeneratorCommand;

class EngezResource extends EngezGeneratorCommand implements EngezInterface
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:resource {resource} 
                                           {--module=} 
                                           {--assets=}
                                           ' . EngezGeneratorCommand::DATA_TYPES_OPTIONS;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make new resource to specific module';

    /**
     * Resource name
     *
     * @var string
     */
    protected string $resourceName;

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

        $this->info('resource has been created successfully');
    }

    /**
     * Set controller info
     * 
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->setModuleName($this->option('module'));

        $this->resourceName = $this->resourceClass($this->argument('resource'));
    }

    /**
     * Create the repository file
     * 
     * @return void
     */
    public function create()
    {
        $resourceName = $this->resourceName;

        $replacements = [
            // replace model name
            '{{ ResourceName }}' => $resourceName,
            // replace module name
            '{{ ModuleName }}' => $this->getModule(),
        ];

        $publishedColumn = config('mongez.repository.publishedColumn');

        if (!$this->optionHasValue('bool') && $publishedColumn) {
            $this->input->setOption('bool', $publishedColumn);
        }

        if (!$this->optionHasValue('int') && $publishedColumn) {
            $this->input->setOption('int', true); // just a placeholder to make the data in the loop
        }

        foreach (static::DATA_TYPES as $type) {
            if (!$this->optionHasValue($type)) {
                $data = '[]';
            } else {
                $typeValue = $this->option($type);

                if ($type === 'int') {
                    if ($typeValue === true) {
                        $typeValue = 'id';
                    } else {
                        $typeValue .= ',id';
                    }
                }

                // replace data
                $data = $this->stubStringAsArray(explode(',', $typeValue));
            }

            if ($type === 'uploads') {
                $type =  $type === 'uploads' ? 'assets' : $type;
            }

            $replacements["{{ $type }}"] = $data;
        }

        $this->putFile("Resources/$resourceName.php", $this->replaceStub('Resources/resource', $replacements), 'Resource');
    }
}
