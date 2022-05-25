<?php

namespace HZ\Illuminate\Mongez\Database\Seeders;

use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use HZ\Illuminate\Mongez\Repository\Concerns\RepositoryTrait;

abstract class SeederManager extends Seeder
{
    use RepositoryTrait;
    /**
     * Repository name
     *
     * @var \Faker\Factory
     */
    protected $faker;

    /**
     * Repository name
     *
     * @var string
     */
    protected const REPOSITORY_NAME = '';

    /**
     * Total of records will be generated
     *
     * @var int
     */
    protected const TOTAL_RECORDS = 5;

    /**
     * name of seeds you need to generated with DOCUMENT_DATA from repository
     * [columnName => demoSeeder::class]
     * column Name must be the same name in the DOCUMENT_DATA
     *
     * @var array
     */
    protected const DOCUMENT_SEEDER = [];

    /**
     * name of seeds you need to generated with MULTI_DOCUMENT_DATA from repository
     * [columnName => demoSeeder::class]
     * column Name must be the same name in the MULTI_DOCUMENT_DATA
     *
     * @var array
     */
    protected const MULTI_DOCUMENT_SEEDER = [];

    /**
     * localization keys
     *
     * @var array
     */
    protected const LOCALIZED_DATA = [];

    /**
     * The default password will be used when key name is password
     *
     * @var string
     */
    protected const DEFAULT_PASSWORD = "123123123";

    /**
     * set default date format
     *
     * @var string
     */
    protected const DATE_FORMAT = 'd-m-Y h:i:s A';

    /**
     * Constructor
     */
    public function __construct()
    {
        // Faker instance.
        $this->faker = Faker::create();

        // Repository Class
        $this->repo = repo(static::REPOSITORY_NAME);
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < static::TOTAL_RECORDS; $i++) {
            // Create new record
            $this->generate();
        }
    }

    /**
     * Create new record
     *
     * @return Illuminate\Database\Eloquent\Model
     */
    public function generate()
    {
        $this->data = new \stdClass();

        $this->setAutoData();

        $this->setDocumentData();

        $this->setMultiDocumentData();

        $this->setData();

        return $this->repo->create((array) $this->data);
    }

    /**
     * Set data to data object
     *
     * @return void
     */
    abstract protected function setData();

    /**
     * set automatically data from repository
     *
     * @param object $this->data
     * @return void
     */
    protected function setAutoData()
    {
        $this->setMainData();

        $this->setLocalizedData();

        $this->setBooleanData();

        $this->setFloatData();

        $this->setIntData();

        $this->setDateData();

        $this->uploads();
    }

    /**
     * get model class from repository
     *
     * @return Illuminate\Database\Eloquent\Model
     */
    public function model()
    {
        $model = $this->getConst('MODEL');

        return new $model;
    }

    /**
     * Get value of constants from repository
     *
     * @param string $const
     * @return mixed
     */
    public function getConst($constName)
    {
        return constant(get_class($this->repo) . '::' . $constName);
    }

    /**
     * Check column name is name
     *
     * @param string $column
     * @return bool
     */
    public function isName($column)
    {
        return in_array($column, ['name', 'firstName', 'lastName']);
    }

    /**
     * Set data
     * @return void
     */
    protected function setMainData()
    {
        $columns = array_merge($this->getConst('DATA'), $this->getConst('STRING_DATA'));

        foreach ($columns as $column) {
            if ($column === 'password') {
                $this->data->password = bcrypt(static::DEFAULT_PASSWORD);

                continue;
            }

            if ($column === 'email') {
                $this->data->email = $this->faker->safeEmail;

                continue;
            }

            if ($this->isName($column)) {
                $this->data->$column = $this->faker->name();

                continue;
            }

            $this->data->$column = $this->faker->text(20);
        }
    }

    /**
     * Set boolean value from BOOLEAN_DATA
     *
     * @return void
     */
    protected function setBooleanData()
    {
        foreach ($this->getConst('BOOLEAN_DATA') as $column) {
            $this->data->$column = $this->faker->boolean();
        }
    }

    /**
     * Set float value from FLOAT_DATA
     *
     * @return void
     */
    protected function setFloatData()
    {
        foreach ($this->getConst('FLOAT_DATA') as $column) {
            $this->data->$column = $this->faker->randomFloat(2);
        }
    }

    /**
     * Set integer value from INTEGER_DATA
     *
     * @return void
     */
    protected function setIntData()
    {
        foreach ($this->getConst('INTEGER_DATA') as $column) {
            $this->data->$column = $this->faker->randomNumber(6);
        }
    }

    /**
     * Set date value from DATE_DATA
     *
     * @return void
     */
    protected function setDateData()
    {
        foreach ($this->getConst('DATE_DATA') as $column) {
            $date = $this->faker->dateTimeThisYear()->format(static::DATE_FORMAT);

            $this->data->$column = $date;
        }
    }

    /**
     * Set date value from LOCALIZED_DATA
     *
     * @return void
     */
    protected function setLocalizedData()
    {
        $localeCodes = config('app.locale_codes');

        $localizationMode = config('mognez.localizationMode', 'array');

        foreach (static::LOCALIZED_DATA as $column) {
            $this->dataLocal = [];

            foreach ($localeCodes as $localeCode) {

                $fakerLocale = $localeCode == 'ar' ? 'ar_SA' : 'en_GB';

                if ($localizationMode == 'array') {
                    $this->dataLocal[] = [
                        'localeCode' => $localeCode,
                        'text' => $this->faker($fakerLocale)->realText(10)
                    ];
                } else {
                    $this->dataLocal[$localeCode] = $this->faker($fakerLocale)->realText(10);
                }
            }

            $this->data->$column = $this->dataLocal;
        }
    }

    /**
     * Set date value from UPLOADS
     * automatically upload random image
     *
     * @return void
     */
    protected function uploads()
    {
        foreach ($this->getConst('UPLOADS') as $column) {

            $this->data->$column = UploadedFile::fake()->image('image.jpg');
        }
    }

    /**
     * Set date value from DOCUMENT_DATA
     *
     * @return void
     */
    protected function setDocumentData()
    {
        foreach ($this->getConst('DOCUMENT_DATA') as $column => $modelClass) {

            if (!isset(static::DOCUMENT_SEEDER[$column])) continue;

            $seeder = static::DOCUMENT_SEEDER[$column];

            $model = (new $seeder)->generate();

            $this->data->$column = $model->id;
        }
    }

    /**
     * Set date value from MULTI_DOCUMENTS_DATA
     *
     * @return void
     */
    protected function setMultiDocumentData()
    {
        foreach ($this->getConst('MULTI_DOCUMENTS_DATA') as $column => $modelClass) {

            if (!isset(static::MULTI_DOCUMENT_SEEDER[$column])) continue;

            $seeder = static::MULTI_DOCUMENT_SEEDER[$column];

            $ids = [];

            for ($i = 0; $i < $this->faker->numberBetween(2, 6); $i++) {
                $model = (new $seeder)->generate();

                $ids[] = $model->id;
            }

            $this->data->$column = $ids;
        }
    }

    /**
     * Create an instance of Faker/Factory with given locale
     *
     * @param string $fakerLocale
     * @return Generator
     */
    private function faker(string $fakerLocale): Generator
    {
        return Faker::create($fakerLocale);
    }
}
