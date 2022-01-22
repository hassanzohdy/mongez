<?php
declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Types;

use HZ\Illuminate\Mongez\Testing\Rules\IsInt;
use HZ\Illuminate\Mongez\Managers\Testing\UnitType;

class IntType extends UnitType
{
    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->addRule([
            new IsInt(),
        ]);
    }
}
