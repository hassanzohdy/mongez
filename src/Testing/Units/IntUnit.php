<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use HZ\Illuminate\Mongez\Testing\Rules\IsIntRule;

class IntUnit extends UnitType
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'int';

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->addRules([
            new IsIntRule(),
        ]);
    }
}
