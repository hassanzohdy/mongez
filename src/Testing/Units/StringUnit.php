<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use HZ\Illuminate\Mongez\Testing\Rules\IsStringRule;

class StringUnit extends UnitType
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'string';

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->addRules([
            new IsStringRule(),
        ]);
    }
}
