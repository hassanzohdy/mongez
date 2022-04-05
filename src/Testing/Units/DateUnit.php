<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

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
            'format' => new DateTimeUnit(),
            'timestamp' => new IntUnit(),
            'humanTime' => new StringUnit(),
            'text' => new StringUnit(),
        ]);
    }
}
