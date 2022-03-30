<?php

namespace HZ\Illuminate\Mongez\Console\Commands;

use HZ\Illuminate\Mongez\Console\EngezInterface;
use HZ\Illuminate\Mongez\Console\EngezGeneratorCommand;

class EngezTest extends EngezGeneratorCommand implements EngezInterface
{
    /**
     * test options
     *
     * @const string
     */
    public const TEST_OPTIONS = '{--type=all}';

    /**
     * test options list
     *
     * @const array
     */
    public const TEST_OPTIONS_LIST = ['type'];

    /**
     * The test types
     *
     * @var array
     */
    const TEST_TYPES = ['admin', 'site', 'all'];

    /**
     * The admin test files
     *
     * @var array
     */
    const ADMIN_TEST_FILES = ['list', 'show', 'create', 'update', 'delete'];

    /**
     * The user test files
     *
     * @var array
     */
    const CUSTOMER_TEST_FILES = ['list', 'show'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engez:test {test} {--module} ' . EngezTest::TEST_OPTIONS;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate unit test files into module';

    /**
     * test name
     *
     * @var string
     */
    protected string $testName;

    /**
     * test type
     * Available Values: site|admin|all
     *
     * @var string
     */
    protected string $testType;

    /**
     * The path of the generated test
     *
     * @var string
     */
    protected string $testPath;

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

        $this->info('test has been created successfully.');
    }

    /**
     * Set test info
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->setModuleName($this->option('module'));

        $this->testType = $this->option('type');
    }

    /**
     * Validate The module name
     *
     * @return void
     */
    public function validateArguments()
    {
        parent::validateArguments();

        $moduleName = $this->getModule();

        if ($this->isAdminTest()) {

            $adminFiles = EngezTest::ADMIN_TEST_FILES;

            foreach ($adminFiles as $adminFile) {

                $capitalizeTestFile = ucfirst($adminFile);

                if ($this->files->exists($this->modulePath('Tests/Admin/' . $capitalizeTestFile . $moduleName . 'Test.php'))) {
                    $this->terminate('You already have this test');
                }
            }
        }

        if ($this->isSiteTest()) {

            $CustomersFiles = EngezTest::CUSTOMER_TEST_FILES;

            foreach ($CustomersFiles as $CustomersFile) {

                $capitalizeTestFile = ucfirst($adminFile);

                if ($this->files->exists($this->modulePath('Tests/Site/' . $capitalizeTestFile . $moduleName . '.php'))) {
                    $this->terminate('You already have this test');
                }
            }
        }

        if (!in_array($this->option('type'), static::TEST_TYPES)) {
            return $this->missingRequiredOption('This test type does not exits, Did you mean? ' . implode(PHP_EOL, static::TEST_TYPES));
        }
    }

    /**
     * Create test Files.
     *
     * @return void
     */
    public function create()
    {
        if ($this->isSiteTest()) {
            $this->createTest('site');
        }

        if ($this->isAdminTest()) {
            $this->createTest('admin');
        }

        // create module unit test
        $this->createUnit();
    }

    /**
     * Create a test for the given type
     *
     * @param  string $testType
     * @return void
     */
    private function createTest(string $testType)
    {
        if ($testType == 'admin') {
            $files = EngezTest::ADMIN_TEST_FILES;
        }else{
            $files = EngezTest::CUSTOMER_TEST_FILES;
        }

        foreach ($files as $file) {
            $capitalizeTestType = ucfirst($testType);
            $capitalizeTestFile = ucfirst($file);
            $moduleName = $this->getModule();
            $moduleRoute = lcfirst($moduleName);
            $model = $this->singular($moduleName);
            $this->testPath = "Tests/{$capitalizeTestType}/{$capitalizeTestFile}{$moduleName}Test.php";
            $this->testName = "{$capitalizeTestFile}{$moduleName}Test.php";
            $className = "{$capitalizeTestFile}{$moduleName}Test";

            $this->info("Creating {$capitalizeTestFile}{$moduleName} test...");

            $replaces = [
                // replace the className name
                '{{ className }}' => $className,
                // replace module name
                '{{ ModuleName }}' => $moduleName,
                // replace module route
                '{{ ModuleRoute }}' => $moduleRoute,
                // replace model name
                '{{ model }}' => $model,
            ];

            // create the file
            $testStub = 'Tests/' . $capitalizeTestType . '/' . $file . '-test';

            $this->putFile($this->testPath, $this->replaceStub($testStub, $replaces), 'Test');
        }
    }

    /**
     * create module unit file
     *
     * @return void
     */
    private function createUnit()
    {
        $moduleName = $this->getModule();
        $model = $this->singular($moduleName);
        $this->testPath = "Tests/Units/{$model}Unit.php";
        $this->testName = "{$model}Unit.php";
        $className = "{$model}Unit";

        $this->info("Creating {$model} Unit...");

        $replaces = [
            // replace the className name
            '{{ className }}' => $className,
            // replace module name
            '{{ ModuleName }}' => $moduleName,
        ];

        // create the file
        $testStub = 'Tests/Units/model-unit';

        $this->putFile($this->testPath, $this->replaceStub($testStub, $replaces), 'Unit');
    }

    /**
     * Determine if current generated test is admin test
     * This is true when type is admin or all
     *
     * @return bool
     */
    protected function isAdminTest(): bool
    {
        return in_array($this->option('type'), ['all', 'admin']);
    }

    /**
     * Determine if current generated test is site test
     * This is true when type is site or all
     *
     * @return bool
     */
    protected function isSiteTest(): bool
    {
        return in_array($this->option('type'), ['all', 'site']);
    }
}
