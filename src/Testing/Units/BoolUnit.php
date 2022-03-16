<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use Hamcrest\Type\IsBoolean;
use HZ\Illuminate\Mongez\Testing\Rules\IsBoolRule;

class BoolUnit extends UnitType
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'bool';

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->addRules([
            new IsBoolRule(),
        ]);
    }
}
