<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use HZ\Illuminate\Mongez\Testing\Rules\IsArrayRule;

class DateUnit extends UnitType
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'date';

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->setUnits([
            'format' => new DateFormatUnit(),
            'timestamp' => new IntUnit(),
            'humanTime' => new StringUnit(),
            'text' => new StringUnit(),
        ]);
    }
}
