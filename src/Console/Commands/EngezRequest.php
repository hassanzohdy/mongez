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
                                        {--module=}
                                        {--trait=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new request to the given module';

    /**
     * The request name.
     *
     * @var string
     */
    protected string $requestName;

    /**
     * The trait name.
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

        $this->info('Request has been created successfully');
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
     * Create method.
     *
     * @return void
     */
    public function create()
    {
        $this->createRequestTrait();

        $this->createRequest();
    }

    /**
     * Create request.
     *
     * @return void
     */
    public function createRequest()
    {
        $replacements = [
            // module name
            '{{ ModuleName }}' => $this->getModule(),
            // request class name
            '{{ RequestClassName }}' => $this->requestName,
        ];

        $path = "Requests/{$this->requestName}.php";

        $stubPath = 'Requests/request';

        if (isset($this->traitName)) {
            $replacements['{{ CommonRulesTrait }}'] = $this->traitName;
            $stubPath = 'Requests/WithTraits/request-with-common-rules';
        }

        $this->putFile($path, $this->replaceStub($stubPath, $replacements));
    }

    /**
     * Create request trait.
     *
     * @return void
     */
    public function createRequestTrait()
    {
        if (!$this->option('trait')) {
            return;
        }

        $this->traitName = "With{$this->singularModule()}CommonRules";

        $modelOptions = [
            'trait' => $this->traitName,
            '--module' => $this->getModule(),
            '--type' => $this->option('trait'),
        ];

        $this->call(
            "engez:trait",
            $modelOptions,
        );
    }
}
