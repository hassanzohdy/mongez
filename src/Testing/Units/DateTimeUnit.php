<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use HZ\Illuminate\Mongez\Testing\Rules\IsDateTimeRule;

class DateTimeUnit extends UnitType
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'dateformat';

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->addRules([
            new IsDateTimeRule(),
        ]);
    }
}
