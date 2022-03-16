<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

class ErrorKeyValueUnit extends ObjectUnit
{
    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();

        $this->setUnits([
            'key' => 'string',
            'value' => 'string',
        ]);
    }
}
