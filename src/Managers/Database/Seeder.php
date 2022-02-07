<?php

namespace HZ\Illuminate\Mongez\Managers\Database;

use Illuminate\Database\Seeder as BaseSeeder;
use HZ\Illuminate\Mongez\Repository\Concerns\RepositoryTrait;

abstract class Seeder extends BaseSeeder
{
    /**
     * We're injecting the repository trait as it will be used
     * for quick access to other repositories
     */
    use RepositoryTrait;
}
