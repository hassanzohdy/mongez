<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use HZ\Illuminate\Mongez\Testing\Units\ObjectUnit;

class NotFoundUnit extends ObjectUnit
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setUnits([
            'key' => 'string',
            'value' => 'string',
        ]);
    }
}
