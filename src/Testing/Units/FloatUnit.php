<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use HZ\Illuminate\Mongez\Testing\Rules\IsFloatRule;

class FloatUnit extends UnitType
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'float';

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->addRules([
            new IsFloatRule(),
        ]);
    }
}
