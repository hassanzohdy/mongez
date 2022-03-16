<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use HZ\Illuminate\Mongez\Testing\Rules\IsArrayRule;

class ArrayUnit extends UnitType
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'array';

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->addRules([
            new IsArrayRule(),
        ]);
    }
}
