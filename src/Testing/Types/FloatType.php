<?php
declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Types;

use HZ\Illuminate\Mongez\Testing\Rules\IsFloat;
use HZ\Illuminate\Mongez\Managers\Testing\UnitType;

class FloatType extends UnitType
{
    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->addRule([
            new IsFloat(),
        ]);
    }
}
