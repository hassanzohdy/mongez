<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use HZ\Illuminate\Mongez\Contracts\Console\EngezInterface;
use HZ\Illuminate\Mongez\Managers\Console\EngezGeneratorCommand;

class EngezSeeder extends EngezGeneratorCommand implements EngezInterface
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:seeder {seeder} 
                                        {--module=} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new seed file to the given module';

    /**
     * The seeder class name 
     * 
     * @var string
     */
    protected string $seederClass;

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

        $this->seederClass = $this->studly($this->argument('seeder') . 'Seeder');
    }

    /**
     * {@inheritDoc}
     */
    public function validateArguments()
    {
        parent::validateArguments();
// dd($this->modulePath("database/Seeders/{$this->seederClass}.php"));
        if ($this->files->exists($this->modulePath("database/Seeders/{$this->seederClass}.php"))) {
            $this->terminate('Seed File Already Exists');
        }
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
            // seeder class name
            '{{ ClassName }}' => $this->seederClass,
            // Repository name
            '{{ repository }}' => $this->repositoryName($this->getModule())
        ];

        $this->putFile("database/Seeders/{$this->seederClass}.php", $this->replaceStub('Seeders/seeder', $replacements), 'Seeder');        

        $this->updateBaseSeedersClass();
    }

    /**
     * Add the seeder class to the base seeders class
     * 
     * @return void
     */
    private function updateBaseSeedersClass()
    {
        $baseSeedersClass = base_path('database/seeders/DatabaseSeeder.php');

        $baseSeedersContent = $this->files->get($baseSeedersClass);

        if (! $this->contains($baseSeedersContent, '$this->call')) {
            $callReplacement = $this->getStub('Seeders/call-method');

            $baseSeedersContent = str_replace('    {', $callReplacement, $baseSeedersContent);
        }

        $replacementLine = '// DatabaseSeeds: DO NOT Remove This Line.';

        $addedSeederClass = '\\App\\Modules\\' . $this->getModule() . '\\Database\\Seeders\\'. $this->seederClass . '::class,';

        $addedSeederClass .= PHP_EOL . "\t\t\t" . $replacementLine;

        $this->files->put($baseSeedersClass, str_replace($replacementLine, $addedSeederClass, $baseSeedersContent));

        $this->info('Base Seeders Class Has Been Updated Successfully.');
    }
}
